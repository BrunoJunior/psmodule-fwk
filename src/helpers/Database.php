<?php
/**
 * 2019 BJ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    BJ <perso@bdesprez.com>
 *  @copyright 2019 BJ
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace bdesprez\psmodulefwk\helpers;

use Countable;
use Db;
use DbPDO;
use Exception;
use mysqli_result;
use PDO;
use PDOStatement;
use PrestaShopDatabaseException;
use bdesprez\psmodulefwk\MyModule;

/**
 * Description of HDatabase
 *
 * @author bruno
 */
class Database
{

    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const PARAM_NULL = '<HDatabase::PARAM_NULL>';
    const PARAM_IS_NULL = '<HDatabase::PARAM_IS_NULL>';
    const PARAM_IS_NOT_NULL = '<HDatabase::PARAM_IS_NOT_NULL>';

    private static $instances = [];

    /**
     * @var MyModule
     */
    private $module;

    /**
     * Retourne l'instance
     * @param MyModule $module
     * @return static
     */
    public static function getInstance(MyModule $module)
    {
        if (!array_key_exists($module->name, static::$instances)) {
            static::$instances[$module->name] = new static($module);
        }
        return static::$instances[$module->name];
    }

    /**
     * Database constructor.
     * @param MyModule $module
     */
    private function __construct(MyModule $module)
    {
        $this->module = $module;
    }

    /**
     * Exécution d'une requête
     * @param string $requete
     * @param array $params
     * @return bool
     * @throws UtilsException
     */
    public function executer($requete, $params = array())
    {
        return Db::getInstance()->execute(static::remplacerParametres($requete, $params), false);
    }

    /**
     * Exécution d'un SELECT
     * @param string $requete
     * @param array $params
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     * @throws UtilsException
     */
    public function rechercher($requete, $params = array())
    {
        return Db::getInstance()->executeS(static::remplacerParametres($requete, $params), true, false);
    }

    /**
     * Echapement d'un paramètre
     * @param mixed $parametre
     * @return mixed
     */
    protected function echaper($parametre)
    {
        return Db::getInstance()->_escape($parametre);
    }

    /**
     * Retourne le dernier id créé en BDD
     * @return int
     */
    public function getDernierIdCree()
    {
        return Db::getInstance()->Insert_ID();
    }

    /**
     * Obtenir le PDO si la connexion utilise PDO
     * Sinon NULL
     * @return PDO
     */
    public function getPDO() {
        $instance = Db::getInstance();
        //#7348 - Vérifier l'existence de la méthode ...
        // Sinon retourne NULL, fonctionnement standard
        if (method_exists($instance, 'getLink') && $instance instanceof DbPDO) {
            return $instance->getLink();
        }
        return NULL;
    }

    /**
     * Requête permettant de remplacer les '?' par les parametres associés
     * @param string $requete
     * @param array $params
     * @return string
     * @throws UtilsException
     */
    protected function remplacerParametres($requete, $params)
    {
        if (!is_array($params) && !$params instanceof Countable) {
            if ($params !== null) {
                $this->getLogger()->logError("Requête : $requete - Paramètres non comptables : " . print_r($params, true));
            }
            $params = [];
        }

        $tbl_requete = explode('?', $requete);
        $requete_finale = '';

        // Il n'y a pas assez de paramètres
        if (substr_count($requete, '?') > count($params)) {
            throw new UtilsException('Nombre de paramètres requis : ' . count($tbl_requete) . ' ; Nombre de paramètres reçus : ' . count($params));
        }
        // Pas de paramètre
        if (count($params) === 0) {
            return $requete;
        }
        // Rien après le dernier ?
        if (end($tbl_requete) === '') {
            $lastIndex = count($tbl_requete) - 1;
            unset($tbl_requete[$lastIndex]);
        }

        $nb_requetes = count($tbl_requete);
        for ($index = 0; $index < $nb_requetes; $index++) {
            $partRequete = $tbl_requete[$index];
            if (isset($params[$index])) {
                $param = $params[$index];
                //#9833 - Comportement bizarre : 0 est considéré dans le tableau
                // en mode non strict … https://stackoverflow.com/questions/13846769/php-in-array-0-value
                if (in_array($param, [static::PARAM_IS_NULL, static::PARAM_IS_NOT_NULL], true)) {
                    $partRequete = Stringer::str_lreplace('=', 'IS', $partRequete);
                }
                if (in_array($param, [static::PARAM_IS_NULL, static::PARAM_NULL], true)) {
                    $valeur = 'NULL';
                } elseif ($param === static::PARAM_IS_NOT_NULL) {
                    $valeur = 'NOT NULL';
                } else {
                    $valeur = '\'' . $this->echaper($param) . '\'';
                }
            } else {
                $valeur = '';
            }
            $requete_finale .= $partRequete . $valeur;
        }

        return $requete_finale;
    }

    /**
     * Vérification d'existance d'une table
     * @param string $tableName
     * @return boolean : TRUE si la table existe
     * @throws PrestaShopDatabaseException
     * @throws UtilsException
     */
    public function tableExists($tableName)
    {
        $results = $this->rechercher("SHOW TABLES LIKE ?", [$tableName]);
        return ($results !== false) && (count($results) > 0);
    }

    /**
     * Utiliser les transactions
     * @return Boolean
     */
    private function withTx()
    {
        $txConf = $this->module->getConf()->getValeur('transaction', false);
        return $this->getPDO() instanceof PDO && $txConf;
    }

    /**
     * Démarrer une transaction (commit la transaction en cours avant s'il y en a une)
     * @param String $origine Informatif - Pour logs
     * @return boolean
     */
    public function demarrerTx($origine = NULL)
    {
        if (!$this->withTx()) {
            return FALSE;
        }
        $messageFinal = '';
        if ($origine) {
            $messageFinal .= '[' . $origine . ']';
        }
        $messageFinal .= "Démarrage transaction via PDO ! ";
        $this->validerTx($origine);
        $this->getLogger()->logInfo($messageFinal, get_class());
        try {
            return $this->getPDO()->beginTransaction();
        } catch (Exception $exc) {
            $this->getLogger()->logWarning($exc->getMessage(), get_class());
            return FALSE;
        }
    }

    /**
     * Valider une transaction
     * @param String $origine Informatif - Pour logs
     * @return boolean
     */
    public function validerTx($origine = NULL)
    {
        if (!$this->withTx()) {
            return FALSE;
        }
        $messageFinal = '';
        if ($origine) {
            $messageFinal .= '[' . $origine . ']';
        }
        $messageFinal .= "Transaction validée via PDO ! ";
        $this->getLogger()->logInfo($messageFinal, get_class());
        try {
            return $this->getPDO()->commit();
        } catch (Exception $exc) {
            $this->getLogger()->logWarning($exc->getMessage(), get_class());
            return FALSE;
        }
    }

    /**
     * Annuler une transaction
     * @param String $origine Informatif - Pour logs
     * @param String $message Message informatif à destination des logs
     * @return boolean
     */
    public function annulerTx($origine = NULL, $message = NULL)
    {
        if (!$this->withTx()) {
            return FALSE;
        }
        $messageFinal = '';
        if ($origine) {
            $messageFinal .= '[' . $origine . ']';
        }
        $messageFinal .= "Transaction annulée via PDO ! ";
        if ($message) {
            $messageFinal .= 'Cause : ' . $message;
        }
        $this->getLogger()->logInfo($messageFinal, get_class());
        try {
            return $this->getPDO()->rollBack();
        } catch (Exception $exc) {
            $this->getLogger()->logWarning($exc->getMessage(), get_class());
            return FALSE;
        }
    }

    /**
     * @return Logger
     */
    private function getLogger() {
        return LoggerFactory::getLogger($this->module->name);
    }
}
