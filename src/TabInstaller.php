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

use AdminController;
use Language;
use Module;
use PrestaShopException;
use PrestaShopModuleException;
use Tab;

/**
 * Description of TabInstaller
 *
 * @author bruno
 */
class TabInstaller
{
    const NO_PARENT = '#NULL#';

    const PARENT_DEFAULT = 'DEFAULT';
    const PARENT_CONFIGURE = 'CONFIGURE';
    const PARENT_IMPROVE = 'IMPROVE';
    const PARENT_SELL = 'SELL';
    
    /**
     * Module
     * @var Module
     */
    private $module;
    
    /**
     * Parent tab name
     * @var string
     */
    private $parentTabName;
    
    /**
     * Associative array name => label
     * @var array
     */
    private $tabs = [];

    /**
     * No parent tabs
     * @var array
     */
    private $noParents = [];
    
    /**
     * Construct
     * @param Module $module
     * @param string $parent
     */
    public function __construct(Module $module, $parent)
    {
        $this->module = $module;
        $this->parentTabName = $parent;
    }

    /**
     * @param $controller
     * @param $label
     * @param null $icon
     * @return $this
     * @throws PrestaShopModuleException
     */
    public function addController($controller, $label, $icon = null)
    {
        if (!is_a($controller, AdminController::class)) {
            throw new PrestaShopModuleException('Controller must be an instance of AdminController');
        }
        $this->tabs[$controller] = ['label' => [], 'icon' => $icon];
        foreach (Language::getLanguages(true) as $lang) {
            $this->tabs[$controller]['label'][$lang['id_lang']] = $label;
        }
        return $this;
    }

    /**
     * Add a tab without parent
     * @param $tabName
     * @return $this
     */
    public function tabWithoutParent($tabName) {
        $this->noParents[$tabName] = true;
        return $this;
    }

    /**
     * @param $controller
     * @return array
     */
    private function getTab($controller) {
        if (array_key_exists($controller, $this->tabs)) {
            return $this->tabs[$controller];
        }
        return [];
    }

    /**
     * Install tabs
     * @return boolean
     */
    public function install()
    {
        $tabParent = $this->addTabIfNotExists($this->parentTabName, $this->getTab($this->parentTabName));
        $isOk = $tabParent !== false;
        foreach ($this->tabs as $classname => $arrtab) {
            if ($classname === $this->parentTabName) {
                continue;
            }
            $isOk &= $this->addTabIfNotExists($classname, $arrtab, $tabParent) !== false;
        }
        return $isOk;
    }

    /**
     * Uninstall tabs
     * @return boolean
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        foreach (array_reverse(array_keys($this->tabs)) as $name) {
            Tab::getInstanceFromClassName($name)->delete();
        }
        return true;
    }

    /**
     * Add a tab if it not exists yet
     * @param string $className
     * @param array $arrTab
     * @param Tab $parent
     * @return Tab|false
     */
    private function addTabIfNotExists($className, array $arrTab, Tab $parent = NULL)
    {
        // Fake parent tab with id -1
        $isWithoutParent = array_key_exists($className, $this->noParents);
        $tab = Tab::getInstanceFromClassName($className);
        if (empty($tab->id)) {
            $tab->module = $this->module->name;
            $tab->active = 1;
            $tab->class_name = $className;
            if ($isWithoutParent) {
                $tab->id_parent = -1;
            } elseif ($parent instanceof Tab) {
                $tab->id_parent = $parent->id;
            }
            $tab->name = $arrTab['label'];
            $tab->icon = $arrTab['icon'];
            if (!$tab->add()) {
                return false;
            }
        } elseif (!empty($arrTab['label'])) {
            $tab->name = $arrTab['label'];
            $tab->update();
        }
        return $tab;
    }

    /**
     * Get tabs for PS1.7 module constructor
     * @return array
     */
    public function toPsTabs() {
        $tabs = [];
        foreach ($this->tabs as $key => $value) {
            $tabs[] = [
                'name' => $value['label'],
                'class_name' => $key,
                'ParentClassName' => $this->parentTabName ?: static::PARENT_DEFAULT,
                'icon' => $value['icon'],
            ];
        }
        return $tabs;
    }

}
