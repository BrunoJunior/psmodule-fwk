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

/**
 * Description of InputCheckbox
 *
 * @author bruno
 */
class InputCheckbox extends InputFormWithOptions
{

    /**
     * Expend options
     * @var array
     */
    private $expandOptions;

    /**
     * Expend options
     * @param array $expendOptions
     * @return $this
     */
    public function setExpandOptions(array $expendOptions)
    {
        $this->expandOptions = $expendOptions;
        return $this;
    }

    /**
     * @return string
     */
    protected function getOptionsKeyInPsFormat()
    {
        return 'values';
    }

    /**
     * Attributs spécifiques au type
     * @return array
     */
    protected function getPrestaShopArrayFormat()
    {
        return array_merge(parent::getPrestaShopArrayFormat(), ['expand' => $this->expandOptions]);
    }

    /**
     * action
     * @return string
     */
    protected function getType()
    {
        return static::TYPE_CHECKBOX;
    }

}
