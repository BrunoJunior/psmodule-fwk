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

use Language;

/**
 * Description of TabInstaller
 *
 * @author bruno
 */
class TabInstaller
{
    const NO_PARENT = '#NULL#';

    const PARENT_DEFAULT = 'DEFAULT';
    const PARENT_SELL = 'SELL';
    const PARENT_SELL_ORDERS = 'AdminParentOrders';
    const PARENT_SELL_CATALOG = 'AdminCatalog';
    const PARENT_SELL_CUSTOMER = 'AdminParentCustomer';
    const PARENT_SELL_SAV = 'AdminParentCustomerThreads';
    const PARENT_SELL_STATS = 'AdminStats';
    const PARENT_SELL_STOCK = 'AdminStock';
    const PARENT_IMPROVE = 'IMPROVE';
    const PARENT_IMPROVE_MODULE = 'AdminParentModulesSf';
    const PARENT_CONFIGURE = 'CONFIGURE';

    /**
     * Module
     * @var MyModule
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
     * Construct
     * @param MyModule $module
     * @param string $parent
     */
    public function __construct(MyModule $module, $parent)
    {
        $this->module = $module;
        $this->parentTabName = $parent;
    }

    /**
     * @param $controller
     * @param string|array $label
     * @param null $icon
     * @param bool $visible
     * @return $this
     */
    public function addController($controller, $label, $icon = null, $visible = true)
    {
        $this->tabs[$controller] = ['label' => $label, 'icon' => $icon, 'visible' => $visible];
        return $this;
    }

    /**
     * Get tabs for PS1.7 module constructor
     * @return array
     */
    public function toPsTabs() {
        $tabs = [];
        foreach ($this->tabs as $key => $value) {
            $tabs[] = [
                'visible' => $value['visible'],
                'name' => $value['label'],
                'class_name' => $key,
                'ParentClassName' => $this->parentTabName ?: static::PARENT_DEFAULT,
                'parent_class_name' => $this->parentTabName ?: static::PARENT_DEFAULT,
                'icon' => $value['icon'],
            ];
        }
        return $tabs;
    }

}
