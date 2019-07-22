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

use DateInterval;
use DateTime;
use Exception;

/**
 * Class Logger
 * @package bdesprez\psmodulefwk\helpers
 */
class Logger
{
    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const MAX_DAY = 60; // 2 months approximately
    const MAX_SIZE = 1048576; // 1 Mo

    private static $dftLevel = self::ERROR;

    /**
     * Nom du module
     * @var string
     */
    private $moduleName;

    /**
     * Niveau min pour le log
     * @var int
     */
    private $level;

    /**
     * Logger constructor.
     * @param $moduleName
     */
    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Getting the log level in string
     * @param $level
     * @return string
     */
    private static function getStrLevel($level)
    {
        switch ($level) {
            case static::DEBUG:
                return 'DEBUG';
            case static::INFO:
                return 'INFO';
            case static::WARNING:
                return 'WARNING';
            case static::ERROR:
                return 'ERROR';
        }
        return "";
    }

    /**
     * Définir le niveau de log par programmation
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Remise du niveau à sa valeur par défaut
     */
    public function resetDefaultLevel()
    {
        $this->level = static::$dftLevel;
    }

    /**
     * Obtenir le niveau de log
     * @return int
     */
    public function getLevel()
    {
        if(defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            $this->level = static::DEBUG;
        }
        if (!isset($this->level)) {
            $this->level = 3;
        }
        return $this->level;
    }

    /**
     * @return string
     */
    private function getShopContextStr()
    {
        $map = [\Shop::CONTEXT_SHOP =>'Boutique', \Shop::CONTEXT_GROUP => 'Groupe', \Shop::CONTEXT_ALL => 'Toutes'];
        return implode(', ', array_filter($map, function ($context) {return \Shop::getContext() & $context;}, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Loggueur
     * @param string $message
     * @param string $filename
     * @param boolean $is_date
     * @param int $level
     */
    public function log($message, $filename = '', $is_date = true, $level = 0)
    {
        $message = ' - [#' . \Shop::getContextShopID() . ' - ' . $this->getShopContextStr() . '] ' . $message;
        if ($level >= $this->getLevel()) {
            if (!empty($filename) && substr($filename, -4) == '.txt') {
                $filename = substr($filename, 0, -4);
            }
            if ($is_date) {
                $filename .= date('Ymd');
            }
            $filename = str_replace('\\', '_', $filename);
            error_log(static::getStrLevel($level) . ' - [' . date('Y-m-d H:i:s') . ']' . $message . PHP_EOL, 3, $this->getLogsDir() . $filename . '.txt');
        }
        if ($level == static::ERROR) {
            error_log('[' . date('d H:i:s') . ']' . $message . PHP_EOL, 3, $this->getLogsDir() . 'errors_' . date('Ym') . '.txt');
        }
    }

    /**
     * Get logs directory
     * @return string
     */
    private function getLogsDir()
    {
        $dir = _PS_MODULE_DIR_ . "/xlposfwk/logs/{$this->moduleName}/";
        if (!file_exists($dir)) {
            mkdir($dir);
        }
            return $dir;
    }

    /**
     * Contenu d'un fichier de log avec son nom
     * @param string $filename
     * @return string
     */
    public function getContenu($filename)
    {
        $filename = $this->getLogsDir() . $filename;
        if (!file_exists($filename)) {
            return NULL;
        }
        return file_get_contents($filename);
    }

    /**
     * Le nom du fichier est la date
     * @param string $date
     * @return string
     */
    public function getFichierAvecDate($date)
    {
        return $this->getContenu($date . '.txt');
    }

    /**
     * Fichiers de log présents
     * @return array
     */
    public function getListeFichiers()
    {
        return scandir($this->getLogsDir());
    }

    /**
     * Loggueur d'erreur
     * @param string $message
     * @param string $filename
     * @param boolean $is_date
     */
    public function logError($message, $filename = '', $is_date = true)
    {
        $this->log($message, $filename, $is_date, static::ERROR);
    }

    /**
     * Loggueur d'exception
     * @param Exception $exc
     * @param string $filename
     * @param boolean $is_date
     */
    public function logException(Exception $exc, $filename = '', $is_date = true)
    {
        $this->log($exc->getMessage(), $filename, $is_date, static::ERROR);
        $this->log($exc->getTraceAsString(), $filename, $is_date, static::ERROR);
    }

    /**
     * Loggueur de warning
     * @param string $message
     * @param string $filename
     * @param boolean $is_date
     */
    public function logWarning($message, $filename = '', $is_date = true)
    {
        $this->log($message, $filename, $is_date, static::WARNING);
    }

    /**
     * Loggueur d'info
     * @param string $message
     * @param string $filename
     * @param boolean $is_date
     */
    public function logInfo($message, $filename = '', $is_date = true)
    {
        $this->log($message, $filename, $is_date, static::INFO);
    }

    /**
     * Loggueur d'objet
     * @param object|array $object
     * @param string $filename
     * @param boolean $is_date
     * @param int $level
     */
    public function logObject($object, $filename = '', $is_date = true, $level = 0)
    {
        $message = '';
        if (is_object($object)) {
            $message = get_class($object) . ' - ';
        }
        $message .= json_encode($object, JSON_PRETTY_PRINT, 3);
        $this->log($message, $filename, $is_date, $level);
    }

    /**
     * Move old files or too big files in old directory
     * @throws Exception
     */
    public function moveOldOrTooBigFiles()
    {
        $oldDir = $this->getLogsDir() . '/old/';
        if (!file_exists($oldDir)) {
            mkdir($oldDir, 0775);
        }
        foreach ($this->getListeFichiers() as $filename) {
            $filename = $this->getLogsDir() . $filename;
            $pathinfos = pathinfo($filename);
            // only txt files
            if (!array_key_exists('extension', $pathinfos) || strtolower($pathinfos['extension']) !== 'txt') {
                continue;
            }
            $now = new DateTime();
            $now->sub(new DateInterval('P' . static::MAX_DAY . 'D'));
            if (filesize($filename) > static::MAX_SIZE || filemtime($filename) < $now->getTimestamp()) {
                // Move to old dir
                rename($filename, $oldDir . $pathinfos['basename']);
            }
        }
    }

    /**
     * Delete old files in old directoy
     * @throws Exception
     */
    public function deleteTooOldFiles()
    {
        $oldDir = $this->getLogsDir() . '/old/';
        foreach (scandir($oldDir) as $filename) {
            $filename = $oldDir . $filename;
            $pathinfos = pathinfo($filename);
            // only txt files
            if (!array_key_exists('extension', $pathinfos) || strtolower($pathinfos['extension']) !== 'txt') {
                continue;
            }
            $now = new DateTime();
            $now->sub(new DateInterval('P' . static::MAX_DAY * 3 . 'D')); // By Default 6 months
            if (filemtime($filename) < $now->getTimestamp()) {
                // Deletion
                unlink($filename);
            }
        }
    }

    /**
     * Logguer la stack trace au moment de l'appel
     * @param string $filename
     * @param boolean $isDate
     */
    public function logStackTrace($filename = null, $isDate = true)
    {
        try {
            throw new Exception('Log stack trace');
        } catch (Exception $exc) {
            $this->logException($exc, $filename, $isDate);
        }
    }
}
