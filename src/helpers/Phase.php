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

use Closure;

/**
 * Description of Phase
 *
 * @author bruno
 */
class Phase
{

    /**
     * Phase name
     * @var string
     */
    private $name;

    /**
     * Phase install function
     * @var Closure
     */
    private $installFunction;

    /**
     * Phase uninstall function
     * @var Closure
     */
    private $uninstallFunction;

    /**
     * Previous phase
     * @var Phase
     */
    private $previous;

    /**
     * Next phase
     * @var Phase
     */
    private $next;

    /**
     * Error message
     * @var string
     */
    private $errorMessage = NULL;

    /**
     * Constructor
     * @param string $name
     * @param Closure $install
     * @param Closure $uninstall
     */
    public function __construct($name, Closure $install = NULL, Closure $uninstall = NULL)
    {
        $this->name = $name;
        if (!isset($install)) {
            $install = function() {return TRUE;};
        }
        $this->installFunction = $install;
        if (!isset($uninstall)) {
            $uninstall = function() {return TRUE;};
        }
        $this->uninstallFunction = $uninstall;
    }

    /**
     * Define the next phase
     * @param Phase $phase
     * @return $this
     */
    public function setNext(Phase $phase)
    {
        $this->next = $phase;
        $phase->previous = $this;
        return $this;
    }

    /**
     * Generate an error message
     * @param string $type (Install, Uninstall)
     * @param string $moduleName
     */
    private function generateError($type, $moduleName)
    {
        $this->errorMessage = "$type $this->name KO !";
        LoggerFactory::getLogger($moduleName)->log($this->errorMessage, '', true, Logger::ERROR);
    }

    /**
     * Run installation
     * @param string $moduleName
     * @return bool
     */
    public function install($moduleName)
    {
        $install = $this->installFunction;
        $isOk = $install();
        LoggerFactory::getLogger($moduleName)->logInfo('Install ' . $this->name . ' : ' . print_r($isOk, true), 'Install');
        if (!$isOk) {
            $this->generateError('Install', $moduleName);
        }
        return $isOk;
    }

    /**
     * Run uninstallation
     * @param string $moduleName
     * @return bool
     */
    public function uninstall($moduleName)
    {
        $uninstall = $this->uninstallFunction;
        $isOk = $uninstall();
        LoggerFactory::getLogger($moduleName)->logInfo('Uninstall ' . $this->name . ' : ' . print_r($isOk, true), 'Install');
        if (!$isOk) {
            $this->generateError('Uninstall', $moduleName);
        }
        return $isOk;
    }

    /**
     * Next phase
     * @return Phase
     */
    public function next()
    {
        return $this->next;
    }

    /**
     * Previous phase
     * @return Phase
     */
    public function prev()
    {
        return $this->previous;
    }

    /**
     * Error message
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

}
