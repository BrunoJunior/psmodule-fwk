<?php
/** @noinspection PhpUndefinedClassInspection */
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

use AdminController;
use Configuration;
use Context;
use Exception;
use HelperForm;
use Language;
use Module;
use PrestaShopException;
use ReflectionClass;
use ReflectionException;
use Tools;
use bdesprez\psmodulefwk\ajax\ModuleProcessAjax;
use bdesprez\psmodulefwk\ajax\ProcessAjax;
use bdesprez\psmodulefwk\conf\ModuleConfiguration;
use bdesprez\psmodulefwk\helpers\StdClass;
use bdesprez\psmodulefwk\hook\Hook;
use bdesprez\psmodulefwk\hook\ObjectHook;

abstract class MyModule extends Module
{
    use SimpleNameTrait;
    use LoggerTrait;

    /**
     * @var array|Hook[]
     */
    private $hooks = [];

    /**
     * @var array|ProcessAjax[]
     */
    private $processes = [];

    /**
     * @var array|ModuleConfiguration[]
     */
    private $configurations = [];

    /**
     * MyModule constructor.
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = Tools::strtolower(static::getSimpleName());
        $this->tab = 'administration';
        $this->author = 'XLSoft';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6.0.9', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();
        foreach ($this->getHooks() as $hook) {
            $this->listenHook($hook);
        }
        foreach ($this->getAutoRegisteredHooks() as $hook) {
            $this->listenHook($hook, true);
        }
        foreach ($this->getObjectHooks() as $hook) {
            $this->listenObjectHook($hook);
        }
        foreach ($this->getAjaxProcesses() as $process) {
            $this->addProcessAjax($process);
        }
        $this->configurations = $this->getModuleConfigurations();
        $this->dependencies = array_merge(['xlposfwk'], $this->getDependencies());
        $this->confirmUninstall = $this->l('Are you sure to unistall?');
    }

    /**
     * @return $this
     */
    public function getModule()
    {
        return $this;
    }

    /**
     * @return array
     */
    protected function getDependencies()
    {
        return [];
    }

    /**
     * @return array|Hook[]
     */
    protected function getHooks()
    {
        return [];
    }

    /**
     * @return array|ObjectHook[]
     */
    protected function getObjectHooks()
    {
        return [];
    }

    /**
     * Des hooks qui se réactivent tout seul même s'ils ont été désactivés
     * @return array|Hook[]
     */
    protected function getAutoRegisteredHooks()
    {
        return [];
    }

    /**
     * @return array|ModuleProcessAjax[]
     */
    protected function getAjaxProcesses()
    {
        return [];
    }

    /**
     * @param Hook $hook
     * @param bool $autoRegister
     * @return static
     * @throws PrestaShopException
     */
    final protected function listenHook(Hook $hook, $autoRegister = false)
    {
        $this->hooks[$hook->getHookName()] = $hook;
        if (!$hook->isRegistered() && $autoRegister) {
            $hook->register();
        }
        return $this;
    }

    /**
     * Adding an object hook in the hooks list
     * In the end, add each defined hooks on the object
     * @param ObjectHook $objectHook
     * @param bool $autoRegister
     * @return $this
     * @throws PrestaShopException
     */
    final protected function listenObjectHook(ObjectHook $objectHook, $autoRegister = false)
    {
        foreach ($objectHook->getHooks() as $hook) {
            $this->listenHook($hook, $autoRegister);
        }
        return $this;
    }

    /**
     * @throws PrestaShopException
     * @return bool
     */
    final protected function installHooks()
    {
        foreach ($this->hooks as $hook) {
            if (!$hook->isRegistered() && !$hook->register()) {
                $this->addError(sprintf('Hook registration error (%s)', $hook->getHookName()));
                return false;
            }
        }
        return true;
    }

    /**
     * Hooks administration
     * @param int $type -1 : unregister, 1 : register
     * @return boolean
     * @throws PrestaShopException
     */
    final public function adminHooks($type = 1)
    {
        $is_ok = true;
        foreach ($this->hooks as $hook) {
            if ($type === 1) {
                $is_ok &= $hook->register();
            } elseif ($type === -1) {
                $is_ok &= $hook->unregister();
            }
        }
        return $is_ok;
    }

    /**
     * @param ProcessAjax $processAjax
     */
    final protected function addProcessAjax(ProcessAjax $processAjax)
    {
        $this->processes[$processAjax->getName(true)] = $processAjax;
    }

    /**
     * Find a hook by a method name
     * @param string $method
     * @return Hook
     * @throws Exception
     */
    final protected function getHook($method)
    {
        $hookname = lcfirst(Tools::substr($method, 4));
        $hook = array_key_exists($hookname, $this->hooks) ? $this->hooks[$hookname] : null;
        if (!$hook instanceof Hook) {
            $this->getLogger()->logInfo("Hook not found {$method}! Unregistration!", $this->name);
            $this->unregisterHook($hookname);
            return null;
        }
        return $hook;
    }

    /**
     * Calling a hook method with Hook object
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        // Authorize only hook… methods
        if (Tools::substr($name, 0, 4) !== 'hook') {
            throw new Exception("Undefined '$name' method");
        }
        $this->getLogger()->logInfo("Trying to find hook {$name}", $this->name);
        $hook = $this->getHook($name);
        if ($hook === null) {
            return null;
        }
        $this->getLogger()->logInfo("Trying tu execute hook {$name}", $this->name);
        $params = is_array($arguments) && count($arguments) > 0 ? $arguments[0] : null;
        $return = $hook->execute($params);
        return $return;
    }

    /**
     * Global ajax action
     */
    final public function ajaxProcessAction()
    {
        $name = Tools::getValue('my_action');
        $this->getLogger()->logInfo("Trying to run ajaxProcessAction({$name})");
        if (array_key_exists($name, $this->processes)) {
            $this->processes[$name]->process();
        }
    }

    /**
     * @return string
     */
    final public function getPath()
    {
        return $this->_path;
    }

    /**
     * @return Context
     */
    final public function getContext()
    {
        return $this->context;
    }

    /**
     * @param $message
     */
    final public function addError($message)
    {
        $this->_errors[] = Tools::displayError($message);
    }

    /**
     * Invalidate the cache
     */
    final public function invalidateCache()
    {
        $filename = _PS_CACHE_DIR_ . 'class_index.php';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * Adding tab CSS
     */
    public function addTabCss()
    {
        return;
    }

    /**
     * This file path
     * @return string
     */
    final public function getFile()
    {
        try {
            $rc = new ReflectionClass($this);
            return $rc->getFileName();
        } catch (ReflectionException $exception) {
            return __FILE__;
        }
    }

    /**
     * @return string
     */
    final public static function getBaseLink()
    {
        $context = Context::getContext();
        if (_PS_VERSION_ >= '1.7') {
            return $context->link->getBaseLink();
        }
        $base = $context->link->getPageLink("index");
        $langId = $context->language->id;
        if ($langId) {
            $iso = '/' . Language::getIsoById($langId).'/';
            $base = str_replace($iso, '', $base);
        }
        return $base . (substr($base, -1) === '/' ? '' : '/');
    }

    /**
     * Le chemin vers le fichier de configuration du module
     * @return string
     */
    protected function getConfFile()
    {
        return _PS_MODULE_DIR_ . $this->name . '/config.json';
    }

    /**
     * @return StdClass
     */
    final public function getConf()
    {
        $conf = new \stdClass();
        if (file_exists($this->getConfFile())) {
            $conf = Tools::jsonDecode(file_get_contents($this->getConfFile()));
        }
        return new StdClass($conf);
    }

    /**
     * @return array|ModuleConfiguration[]
     */
    abstract protected function getModuleConfigurations();

    /**
     * @return string
     */
    protected function getContentAfterConfigurationForms()
    {
        return '';
    }

    /**
     * Init Fields form array
     * @return array
     */
    private function getFieldsForms()
    {
        $fieldsForm = array();
        foreach ($this->configurations as $configuration) {
            $fieldsForm[]['form'] = $configuration->render()->toPrestaShopFormat();
        }
        return $fieldsForm;
    }

    /**
     * Affichage des confs
     * @return string
     */
    private function renderForm()
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;  // false -> remove toolbar
        $helper->submit_action = 'submit' . $this->name;

        foreach ($this->configurations as $configuration) {
            $configuration->fillForm($helper);
        }
        $formHtml = $helper->generateForm($this->getFieldsForms());
        return $formHtml;
    }

    /**
     * Traitement des retours de formulaire
     * @return null|string
     */
    private function treatSubmit()
    {
        $output = null;
        foreach ($this->configurations as $configuration) {
            if (Tools::isSubmit($configuration->getSubmitName())) {
                try {
                    $output = $configuration->treatSubmit();
                } catch (Exception $exception) {
                    $output = $this->displayError($exception->getMessage());
                }
                break;
            }
        }
        return $output;
    }

    /**
     * Affichage Configuration
     */
    public function getContent()
    {
        return $this->treatSubmit() . $this->renderForm() . $this->getContentAfterConfigurationForms();
    }

    /**
     * Installation complémentaire
     * @return bool
     */
    protected function complementaryInstall()
    {
        return true;
    }

    /**
     * Installation
     * @return boolean
     * @throws PrestaShopException
     */
    final public function install()
    {
        if (!parent::install()) {
            $this->addError($this->l('PrestaShop installation error!'));
            return false;
        }
        $hooks = $this->installHooks();
        if (!$hooks) {
            $this->addError($this->l('Error during hooks installation!'));
        }
        $confs = true;
        foreach ($this->configurations as $configuration) {
            $confs &= $configuration->install();
        }
        if (!$confs) {
            $this->addError($this->l('Error during configurations installation!'));
        }
        return $hooks && $confs && $this->complementaryInstall();
    }

    /**
     * Désinstallation complémentaire
     * @return bool
     */
    protected function complementaryUninstall()
    {
        return true;
    }

    /**
     * Désinstallation
     * @return boolean
     */
    final public function uninstall()
    {
        if (!$this->complementaryUninstall()) {
            return false;
        }
        foreach ($this->configurations as $configuration) {
            if (!$configuration->uninstall()) {
                return false;
            }
        }
        return parent::uninstall();
    }

}
