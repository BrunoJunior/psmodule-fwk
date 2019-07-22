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
 * Description of InputFormValues
 *
 * @author bruno
 */
abstract class InputFormValues extends InputForm
{

    /**
     * This is only useful if type == radio
     * @var array
     */
    protected $values;

    /**
     * Get the label of a value in values
     * @param mixed $value
     * @return string
     */
    public function getValueLabel($value)
    {
        $label = null;
        if (is_array($this->values)) {
            foreach ($this->values as $infos) {
                if (array_key_exists('value', $infos) && array_key_exists('label', $infos) && $infos['value'] === $value) {
                    $label = $infos['label'];
                    break;
                }
            }
        }
        return $label;
    }

    /**
     * Set the label of a value in values
     * @param mixed $value
     * @param string $label
     * @return $this
     */
    public function setValueLabel($value, $label)
    {
        if (is_array($this->values)) {
            foreach ($this->values as $index => $infos) {
                if (array_key_exists('value', $infos) && $infos['value'] === $value) {
                    $this->values[$index]['label'] = $label;
                    break;
                }
            }
        }
        return $this;
    }

}
