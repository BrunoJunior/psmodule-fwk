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

namespace bdesprez\psmodulefwk;

use bdesprez\psmodulefwk\helpers\Logger;
use bdesprez\psmodulefwk\helpers\LoggerFactory;

/**
 * Class Autoloader
 * @package bdesprez\psmodulefwk
 */
class Autoloader
{
    /**
     * Chemin vers racine PS
     * @var string
     */
    private static $PS_ROOT_DIR;

    /**
     * Préfixe des namespaces
     * @var string
     */
    private $prefix;

    /**
     * Nom du répertoire racine
     * @var string
     */
    private $dirName;

    /**
     * Nom du répertoire pour les modèles du module
     * @var string
     */
    private $modelsDirName;

    /**
     * Nom du module
     * @var string
     */
    private $moduleName;

    /**
     * @param $namespacesStart
     * @param $moduleName
     * @param string $namespaceRootDirName
     * @param string $modelsDirName
     * @return bool
     */
    public static function registerModule($namespacesStart, $moduleName, $namespaceRootDirName = 'src', $modelsDirName = 'models') {
        return (new static($namespacesStart, $moduleName, $namespaceRootDirName, $modelsDirName))->load();
    }

    /**
     * Autoloader constructor.
     * @param $prefix
     * @param $moduleName
     * @param string $dirName
     * @param string $modelsDirName
     */
    private function __construct($prefix, $moduleName, $dirName = 'src', $modelsDirName = 'models')
    {
        $this->prefix = $prefix;
        $this->moduleName = $moduleName;
        $this->dirName = $dirName;
        $this->modelsDirName = $modelsDirName;
        if (static::$PS_ROOT_DIR === null) {
            static::$PS_ROOT_DIR = dirname(dirname(dirname(__DIR__)));
        }
    }

    /**
     * @return Logger
     */
    private function getLogger()
    {
        return LoggerFactory::getLogger($this->moduleName);
    }

    /**
     * @param $class
     * @return bool
     */
    private function register($class) {
        // Remove first \ if present
        $class_name = ltrim($class, '\\');
        // base directory for the namespace prefix
        $base_dir = implode(DIRECTORY_SEPARATOR, [static::$PS_ROOT_DIR, 'modules', $this->moduleName, $this->dirName, '']);
        // does the class use the namespace prefix?
        $len = strlen($this->prefix);
        if (strncmp($this->prefix, $class_name, $len) !== 0) {
            // no, move to the next registered autoloader
            return false;
        }
        $relative_class_name = substr($class_name, $len);
        // Récupération du chemin avec les namespaces
        $chemin = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class_name) . '.php';
        if (!file_exists($chemin)) {
            return false;
        }
        require $chemin;
        return true;
    }

    /**
     * @param $class
     * @return bool
     */
    private function registerModels($class) {
        // models base directory
        $base_dir = implode(DIRECTORY_SEPARATOR, [static::$PS_ROOT_DIR, 'modules', $this->moduleName, $this->dirName, $this->modelsDirName, '']);
        $chemin = $base_dir . $class . '.php';
        if (!file_exists($chemin)) {
            return false;
        }
        require $chemin;
        return true;
    }

    /**
     *
     */
    private function registerShutdown() {
        $error = error_get_last();
        if ($error !== NULL) {
            // On evite de surcharger les traces à cause des erreurs SMARTY ou PrestaShop
            $isModuleFile = strstr($error['file'], "modules/{$this->moduleName}/");
            if ($isModuleFile === FALSE) {
                return;
            }
            //il y a eu une FATAL ERROR on essaie de faire le rollback
            $info = "[Error] trapée dans handleShutdown file:" . $error['file'] . " | ln:" . $error['line'] . " | msg:" . $error['message'] . PHP_EOL;
            $this->getLogger()->logError($info);
            $this->getLogger()->logInfo(json_encode($_POST));
        }
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     */
    private function registerErrorHandler($errno, $errstr, $errfile, $errline)
    {
        // On ne fait rien si l'erreur n'a rien à voir avec le module
        $isModuleFile = strstr($errfile, "modules/{$this->moduleName}/");
        if ($isModuleFile === false) {
            return false;
        }
        //dans tous les cas on ecrit dans les logs d'apache
        error_log($errno . ' - ' . $errstr . ' - ' . $errfile . ' - ' . $errline);
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $message = "ERREUR [$errno] $errstr\n";
                $message .="Erreur fatale à la ligne " . $errline . "du fichier" . $errfile . "\n";
                $this->getLogger()->logError($message);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $message = "WARNING [$errno] $errstr\n";
                $message .="Warning à la ligne" . $errline . " du fichier " . $errfile . "\n";
                $this->getLogger()->logError($message);
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $message = "NOTICE [$errno] $errstr\n";
                $message .="Notice à la ligne " . $errline . "du fichier " . $errfile . "\n";
                $this->getLogger()->logError($message);
                break;

            case E_DEPRECATED:
                $message = "DEPRECATED [$errno] $errstr\n";
                $message .="Deprecated à la ligne " . $errline . "du fichier " . $errfile . "\n";
                $this->getLogger()->logError($message);
                break;

            default:
                $message = "INCONNU [$errno] $errstr\n";
                $message .="Erreur inconnue à la ligne " . $errline . " du fichier " . $errfile . "\n";
                $this->getLogger()->logError($message);
                break;
        }
        return true;
    }

    /**
     * Enregistre les autoload
     * @return bool
     */
    private function load() {
        $isOk = spl_autoload_register(function ($class) {return $this->register($class);});
        $isOk &= spl_autoload_register(function ($class) {return $this->registerModels($class);});
        register_shutdown_function(function () {$this->registerShutdown();});
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {return $this->registerErrorHandler($errno, $errstr, $errfile, $errline);});
        return $isOk;
    }
}
