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

use Db;
use ObjectModel;

/**
 * Description of Migration
 *
 * @author bruno
 */
abstract class Migration
{
    //#10017

    /**
     * Model
     * @var ObjectModel
     */
    private $model;
    
    /**
     * Take the model
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * @return ObjectModel $model
     */
    abstract protected function getModel();

    /**
     * Model table name
     * @return string
     */
    public function getTable()
    {
        $model = $this->model;
        return $model::$definition['table'];
    }

    /**
     * Model table name
     * @return string
     */
    public function getPrimary()
    {
        $model = $this->model;
        return $model::$definition['primary'];
    }

    /**
     * SQL collumns
     * @return string
     */
    abstract protected function getCols();

    /**
     * Creation
     * @return bool
     */
    public function up()
    {
        $sqlTable = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->getTable() . '` (
                            `' . $this->getPrimary() . '` int(10) NOT NULL AUTO_INCREMENT,
                            ' . $this->getCols() . ',
                            PRIMARY KEY (`' . $this->getPrimary() . '`)
                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $isOk = Db::getInstance()->execute($sqlTable);

        if ($this->model->isMultishop()) {
            $sqlTableShop = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $this->getTable() . '_shop` (
                                    `' . $this->getPrimary() . '` int(10) NOT NULL,
                                    `id_shop` int(10) NOT NULL,
                                    UNIQUE KEY `' . $this->getTable() . '_shop_index` (`' . $this->getPrimary() . '`,`id_shop`)
                            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
            $isOk &= Db::getInstance()->execute($sqlTableShop);
        }
        return $isOk;
    }

    /**
     * Deletion
     * @return bool
     */
    public function down()
    {
        $isOk = true;
        if ($this->model->isMultishop()) {
            $isOk = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->getTable() . '_shop`;');
        }
        $isOk &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $this->getTable() . '`;');
        return $isOk;
    }

}
