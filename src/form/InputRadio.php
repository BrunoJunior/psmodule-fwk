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

namespace bdesprez\psmodulefwk\form;

use bdesprez\psmodulefwk\ILabeledKeys;

/**
 * Description of InputRadio
 *
 * @author bruno
 */
class InputRadio extends InputFormWithValues
{

    /**
     * This is only useful if type == radio. It displays a "yes or no" choice
     * @var boolean
     */
    private $isBool;

    /**
     * InputRadio constructor.
     * @param ILabeledKeys $labeledKeys
     * @param $name
     * @param array|InputFormValue[] $values
     * @param bool $isBool
     */
    public function __construct(ILabeledKeys $labeledKeys, $name, array $values, $isBool = false)
    {
        parent::__construct($labeledKeys, $name, $values);
        $this->isBool = $isBool;
    }

    /**
     * Attributs spécifiques au type
     * @return array
     */
    protected function getPrestaShopArrayFormat()
    {
        return array_merge(parent::getPrestaShopArrayFormat(), ['is_bool' => $this->isBool]);
    }

    /**
     * radio
     * @return string
     */
    protected function getType()
    {
        return static::TYPE_RADIO;
    }

}
