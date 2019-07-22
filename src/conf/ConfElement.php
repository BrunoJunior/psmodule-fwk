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

namespace bdesprez\psmodulefwk\conf;

class ConfElement
{
    /**
     * Nom de la conf
     * @var string
     */
    private $name;

    /**
     * Valeur par défaut
     * @var mixed
     */
    private $defaultValue;

    /**
     * Pour tous les shops ?
     * @var bool
     */
    private $allShops = true;

    /**
     * Valeurs possibles
     * @var array
     */
    private $possibleValues;

    /**
     * ConfElement constructor.
     * @param $name
     * @param $defaultValue
     * @param array $possibleValues
     */
    public function __construct($name, $defaultValue, $possibleValues = [])
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
        $this->possibleValues = $possibleValues;
    }

    /**
     * @return bool
     */
    public function isAllShops()
    {
        return $this->allShops;
    }

    /**
     * @param bool $allShops
     * @return static
     */
    public function setAllShops($allShops = true)
    {
        $this->allShops = $allShops;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return array
     */
    public function getPossibleValues()
    {
        return $this->possibleValues;
    }

}
