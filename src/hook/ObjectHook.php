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

use Closure;
use PrestaShopException;
use bdesprez\psmodulefwk\helpers\LoggerFactory;
use bdesprez\psmodulefwk\MyModule;
use bdesprez\psmodulefwk\TranslationTrait;

/**
 * Description of ObjectHook
 *
 * @author bruno
 */
abstract class ObjectHook
{
    use TranslationTrait;

    const TYPE_ADD = 'Add';
    const TYPE_UPD = 'Update';
    const TYPE_DEL = 'Delete';
    const EVT_BEFORE = 'Before';
    const EVT_AFTER = 'After';

    /**
     * XLPos module
     * @var MyModule
     */
    protected $module;

    /**
     * Hooks
     * @var ClosureHook[];
     */
    private $hooks = [];

    /**
     * Construct
     * @param MyModule $module
     */
    public function __construct(MyModule $module)
    {
        $this->module = $module;
        $closures = [
            $this->getHookKey(static::TYPE_ADD, static::EVT_BEFORE) => function() {$this->getAddBeforeFunction();},
            $this->getHookKey(static::TYPE_ADD, static::EVT_AFTER) => function() {$this->getAddAfterFunction();},
            $this->getHookKey(static::TYPE_UPD, static::EVT_BEFORE) => function() {$this->getUpdateBeforeFunction();},
            $this->getHookKey(static::TYPE_UPD, static::EVT_AFTER) => function() {$this->getUpdateAfterFunction();},
            $this->getHookKey(static::TYPE_DEL, static::EVT_BEFORE) => function() {$this->getDeleteBeforeFunction();},
            $this->getHookKey(static::TYPE_DEL, static::EVT_AFTER) => function() {$this->getDeleteAfterFunction();},
        ];
        // Instanciate defined hooks
        foreach ($closures as $key => $closure) {
            if (!$closure instanceof Closure) {
                continue;
            }
            $this->hooks[] = new ClosureHook($module, $this->getHookname($key), $closure);
        }
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
     * The short classname
     * @return string
     */
    public static function getSimpleClass()
    {
        return static::getSimpleName();
    }

    /**
     * The hook name for a type and an event
     * @param string $hookKey
     * @return string
     */
    protected function getHookname($hookKey)
    {
        return 'actionObject' . static::getSimpleClass() . $hookKey;
    }

    /**
     * Hook key
     * @param string $type
     * @param string $evt
     * @return string
     */
    private function getHookKey($type, $evt)
    {
        return $type . $evt;
    }

    /**
     * Before add hook, has to return a Closure
     */
    protected function getAddBeforeFunction()
    {
        
    }

    /**
     * After add hook, has to return a Closure
     */
    protected function getAddAfterFunction()
    {
        
    }

    /**
     * Before update hook, has to return a Closure
     */
    protected function getUpdateBeforeFunction()
    {
        
    }

    /**
     * After update hook, has to return a Closure
     */
    protected function getUpdateAfterFunction()
    {
        
    }

    /**
     * Before delete hook, has to return a Closure
     */
    protected function getDeleteBeforeFunction()
    {
        
    }

    /**
     * After delete hook, has to return a Closure
     */
    protected function getDeleteAfterFunction()
    {
        
    }

    /**
     * Is the hooks registered on module ?
     * @param ClosureHook $hook
     * @return bool
     */
    public function isRegistered(ClosureHook $hook)
    {
        return $this->module->isRegisteredInHook($hook->getHookName());
    }

    /**
     * Register the hooks in the module
     * @return bool Registration ok
     * @throws PrestaShopException
     */
    public function register()
    {
        $isOk = true;
        foreach ($this->hooks as $hook) {
            $isHookOk = true;
            if (!$this->isRegistered($hook)) {
                $isHookOk = $this->module->registerHook($hook->getHookName());
                $isOk &= $isHookOk;
            }
            if (!$isHookOk) {
                LoggerFactory::getLogger($this->module->name)->logError('Hook registration [' . $hook->getHookName() . '] KO.');
            }
        }
        return $isOk;
    }

    /**
     * Unregister the hook in the module
     * @return bool
     */
    public function unregister()
    {
        $isOk = true;
        foreach ($this->hooks as $hook) {
            $isHookOk = true;
            if ($this->isRegistered($hook)) {
                $isHookOk = $this->module->unregisterHook($hook->getHookName());
                $isOk &= $isHookOk;
            }
            if (!$isHookOk) {
                LoggerFactory::getLogger($this->module->name)->logError('Hook unregistration [' . $hook->getHookName() . '] KO.');
            }
        }
        return $isOk;
    }

    /**
     * Defined hooks list
     * @return ClosureHook[]
     */
    public function getHooks()
    {
        return $this->hooks;
    }
    
    /**
     * The module
     * @return MyModule
     */
    public function getModule()
    {
        return $this->module;
    }

}
