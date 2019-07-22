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

use Context;
use Language;
use Module;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tab;

/**
 * Description of TabInstaller
 *
 * @author bruno
 */
class TabInstaller
{
    const NO_PARENT = '#NULL#';
    
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
     * @param array $tabs
     * @param string $parent
     */
    public function __construct(Module $module, array $tabs, $parent)
    {
        $this->module = $module;
        $this->tabs = $tabs;
        $this->parentTabName = $parent;
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
     * Install tabs
     * @return boolean
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        $startLang = Context::getContext()->language;
        $names = [];
        foreach (Language::getLanguages(true) as $lang) {
            Context::getContext()->language = new Language($lang['id_lang']);
            foreach ($this->tabs as $name => $label) {
                $names[$name][$lang['id_lang']] = $label;
            }
        }
        Context::getContext()->language = $startLang;
        $tabParent = $this->addTabIfNotExists($this->parentTabName, $names[$this->parentTabName]);
        $isOk = $tabParent !== false;
        foreach ($names as $classname => $name) {
            if ($classname === $this->parentTabName) {
                continue;
            }
            $isOk &= $this->addTabIfNotExists($classname, $name, $tabParent) !== false;
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
     * @param array<string> $names
     * @param Tab $parent
     * @return Tab|false
     */
    private function addTabIfNotExists($className, $names, Tab $parent = NULL)
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
            $tab->name = $names;
            if (!$tab->add()) {
                return false;
            }
        } else {
            $tab->name = $names;
            $tab->update();
        }
        return $tab;
    }

}
