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

use PrestaShopException;
use Shop;

/**
 * Description of ShopContextSaver
 *
 * @author bruno
 */
class ShopContextSaver
{
    /**
     * @var int
     */
    private $shopContext;

    /**
     * @var int
     */
    private $id;

    private static function isPrestaShop() {
        return class_exists("\\Shop");
    }

    /**
     * Protected constructor for singleton
     */
    protected function __construct()
    {
        //#11345 - Only for PrestaShop
        if (!static::isPrestaShop()) {
            return;
        }
        $this->shopContext = Shop::getContext();
        $this->id = Shop::getContextShopID();
    }

    /**
     * Getting the singleton instance
     * @param bool $allShops
     * @return static
     * @throws PrestaShopException
     */
    public static function getInstance($allShops = false)
    {
        $instance = new static();
        if ($allShops) {
            $instance->setContextAllShops();
        }
        return $instance;
    }

    /**
     * Go back to the original shop context
     * @return $this
     * @throws PrestaShopException
     */
    public function rollbackShopContext()
    {
        if (static::isPrestaShop()) {
            Shop::setContext($this->shopContext, $this->id);
        }
        return $this;
    }

    /**
     * Put the all shops context
     * @return $this
     * @throws PrestaShopException
     */
    public function setContextAllShops() {
        if (static::isPrestaShop()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return $this;
    }

    /**
     * Switch to a specific shop
     * @param int|null $shopId null = all
     * @return $this
     * @throws PrestaShopException
     */
    public function switchToShop($shopId) {
        if ($shopId === null) {
            return $this->setContextAllShops();
        }
        if (static::isPrestaShop()) {
            Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
        }
        return $this;
    }

}
