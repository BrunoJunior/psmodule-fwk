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

namespace bdesprez\psmodulefwk\hook;

use PrestaShopException;
use bdesprez\psmodulefwk\MyModule;
use bdesprez\psmodulefwk\TranslationTrait;

/**
 * Description of Hook
 *
 * @author bruno
 */
abstract class Hook
{
    use TranslationTrait;

    /**
     * module
     * @var MyModule
     */
    protected $module;

    /**
     * Log before run
     * @var bool
     */
    private $logBefore = false;

    /**
     * Construct
     * @param MyModule $module
     * @param mixed $initParams,... Optional series of init parameters
     */
    public function __construct(MyModule $module)
    {
        $this->module = $module;
        $initParams = [];
        if (func_num_args() > 1) {
            $initParams = func_get_args();
            array_shift($initParams);
        }
        $this->init($initParams);
    }

    /**
     * Child initialisation
     * @param array $initParams
     */
    protected function init(array $initParams)
    {
        
    }
    
    /**
     * MyModule module
     * @return MyModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Is the hook registered on module ?
     * @return bool
     */
    public function isRegistered()
    {
        return $this->module->isRegisteredInHook($this->getHookName());
    }

    /**
     * Register the hook in the module only if it's active
     * @return bool Registration ok
     * @throws PrestaShopException
     */
    public function register()
    {
        $isOk = true;
        if ($this->isActive() && !$this->isRegistered()) {
            $this->getLogger()->logInfo('Hook registration [' . $this->getHookName() . ']', 'Hooks');
            $isOk = $this->module->registerHook($this->getHookName());
        } elseif (!$this->isActive()) {
            // unregister the hook if it's not active
            $isOk = $this->unregister();
        }
        if (!$isOk) {
            $this->getLogger()->logError('Hook registration [' . $this->getHookName() . '] KO.', 'Hooks');
        }
        return $isOk;
    }

    /**
     * Unregister the hook in the module
     */
    public function unregister()
    {
        $isOk = true;
        if ($this->isRegistered()) {
            $this->getLogger()->logInfo('Hook unregistration [' . $this->getHookName() . ']', 'Hooks');
            $isOk = $this->module->unregisterHook($this->getHookName());
        }
        if (!$isOk) {
            $this->getLogger()->logError('Hook unregistration [' . $this->getHookName() . '] KO.', 'Hooks');
        }
        return $isOk;
    }

    /**
     * Hook execution
     * @param array $params
     * @return mixed
     */
    public function execute($params)
    {
        #10912
        if (!$this->isActive()) {
            $this->unregister();
            return null;
        }
        if (!$this->isValid($params)) {
            return null;
        }
        $modeDev = defined('_PS_MODE_DEV_') && _PS_MODE_DEV_;
        if ($this->logBefore || $modeDev) {
            $this->getLogger()->log(static::getClass() . ' [' . $this->getHookName() . ']', 'Hooks');
            $this->getLogger()->log(print_r($params, true), 'Hooks');
        }
        $result = $this->run($params);
        if ($this->logBefore || $modeDev) {
            $this->getLogger()->log(static::getClass() . ' [' . $this->getHookName() . '] - FIN', 'Hooks');
        }
        return $result;
    }

    /**
     * @param bool $logBefore
     */
    public function setLogBefore($logBefore = true)
    {
        $this->logBefore = $logBefore;
    }

    /**
     * @param array $params
     * @return bool
     */
    protected function isValid($params) {
        return true;
    }

    /**
     * @return bool
     */
    protected function isActive() {
        #10912
        return true;
    }

    /**
     * The classname
     * @return string
     */
    public static function getClass()
    {
        return get_called_class();
    }

    /**
     * The hook name
     * @return string
     */
    abstract public function getHookName();

    /**
     * Functionnal part
     * @param array $params
     * @return mixed
     */
    abstract protected function run($params);
}
