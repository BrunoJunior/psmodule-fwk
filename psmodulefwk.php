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

if (!defined('_PS_VERSION_'))
    exit;

require_once __DIR__ . '/autoload.php';

use bdesprez\psmodulefwk\helpers\Logger;
use bdesprez\psmodulefwk\helpers\LoggerFactory;

/**
 * Class PsModuleFwk
 * Module de base pour les autres modules
 */
class PsModuleFwk extends Module
{
    // Version interne modifiée automatiquement lors du packaging
    public $internalVersion = '201907191117';

    /**
     * Constructeur
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'psmodulefwk';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'BrunoJunior <pro@bdesprez.com>';
        $this->need_instance = 0;
        // Compatible 1.7
        $this->ps_versions_compliancy = array('min' => '1.6.1.1', 'max' => '1.7.99.99');
        parent::__construct();
        $this->displayName = $this->l('BJ Module Framework');
        $this->description = $this->l('Base module for all BJ\'s modules. Toolset for BJ\'s modules.');
        $this->confirmUninstall = $this->l('Are you sure to unistall?');
        $hooks = ['actionAdminControllerSetMedia', 'actionObjectAddBefore', 'actionObjectUpdateBefore'];
        foreach ($hooks as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                $this->registerHook($hook);
            }
        }
    }

    /**
     * Logger
     * @return Logger
     */
    private function getLogger() {
        return LoggerFactory::getLogger($this->name);
    }

    /**
     * On ne peut pas désinstaller ce module si d'autres en dépendent
     * @return bool
     */
    public function uninstall()
    {
        foreach (Module::getModulesInstalled() as $arrModule) {
            $module = Module::getInstanceByName($arrModule['name']);
            if (in_array($this->name, $module->dependencies)) {
                $this->_errors[] = Tools::displayError($this->l('You can\'t uninstall this module. Some others depends on it!'));
                return false;
            }
        }
        return parent::uninstall();
    }

    /**
     * @return true|void
     */
    public function hookActionAdminControllerSetMedia()
    {
        $moduleName = \bdesprez\psmodulefwk\helpers\StdClass::getInstance((object) $this->context->smarty->tpl_vars)->get('module_name')->getValeur('value');
        $this->getLogger()->log('Module context : ' . $moduleName);
        if ($moduleName && in_array($this->name, Module::getInstanceByName($moduleName)->dependencies)) {
            $this->context->controller->addJS($this->_path . '/views/js/main.js');
        }
    }

    /**
     * @param array $params
     */
    public function hookActionObjectAddBefore($params)
    {
        if ($this->getLogger()->getLevel() === Logger::DEBUG) {
            $this->getLogger()->log('Add', "ObjectModel");
            $this->getLogger()->logObject($params['object'], "ObjectModel");
            $this->getLogger()->logStackTrace("ObjectModel");
        }
    }

    /**
     * @param array $params
     */
    public function hookActionObjectUpdateBefore($params)
    {
        if ($this->getLogger()->getLevel() === Logger::DEBUG) {
            $this->getLogger()->log('Update', "ObjectModel");
            $this->getLogger()->logObject($params['object'], "ObjectModel");
            $this->getLogger()->logStackTrace("ObjectModel");
        }
    }
}
