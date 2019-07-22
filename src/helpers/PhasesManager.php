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
 * Description of PhasesManager
 *
 * @author bruno
 */
class PhasesManager
{
    /**
     * First phase
     * @var Phase
     */
    private $first = NULL;
    /**
     * Last phase
     * @var Phase
     */
    private $last = NULL;
    
    /**
     * Actual phase
     * @var Phase
     */
    private $actual = NULL;
    
    /**
     * onError action
     * @var Closure
     */
    private $onError;

    /**
     * Nom du module
     * @var string
     */
    private $moduleName;

    /**
     * Construct with onError action
     * @param string $moduleName
     * @param Closure $onError
     */
    public function __construct($moduleName, Closure $onError = NULL)
    {
        $this->moduleName = $moduleName;
        if (!isset($onError)) {
            $onError = function() {};
        }
        $this->onError = $onError;
    }
    
    /**
     * Adding a phase
     * @param Phase $phase
     * @return $this
     */
    public function addPhase(Phase $phase) 
    {
        if (!isset($this->first)) {
            $this->first = $phase;
        } else {
            $this->last->setNext($phase);
        }
        $this->last = $phase;
        return $this;
    }

    /**
     * Install or uninstall
     * @param Phase $startingElement
     * @param string $mode (install / uninstall)
     * @param bool $reverseOnError
     * @return bool
     * @throws UtilsException
     */
    private function _run($startingElement, $mode, $reverseOnError = TRUE) {
        LoggerFactory::getLogger($this->moduleName)->logInfo("Starting $mode", "PhasesManager");
        if (!isset($startingElement)) {
            throw new UtilsException("Nothing to $mode !");
        }
        $this->actual = $startingElement;
        $nextMethod = $mode === 'install' ? 'next' : 'prev';
        do {
            $isOk = $this->actual->$mode($this->moduleName);
            $this->actual = $this->actual->$nextMethod();
        } while ($this->actual && $isOk);
        if (!$isOk) {
            LoggerFactory::getLogger($this->moduleName)->logInfo("Error during $mode - Rollback", "PhasesManager");
            $onError = $this->onError;
            $onError($this->actual->getErrorMessage());
            if ($reverseOnError) {
                $reverseMode = $mode === 'install' ? 'uninstall' : 'install';
                $prevMethod = $mode === 'install' ? 'prev' : 'next';
                $this->_run($this->actual->$prevMethod(), $reverseMode, FALSE);
            }
        }
        return $isOk;
    }

    /**
     * Run install
     * @return bool
     * @throws UtilsException
     */
    public function runInstall() {
        return $this->_run($this->first, 'install');
    }

    /**
     * Run uninstall
     * @return bool
     * @throws UtilsException
     */
    public function runUninstall() {
        Cache::setUninstall($this->moduleName);
        return $this->_run($this->last, 'uninstall');
    }
}
