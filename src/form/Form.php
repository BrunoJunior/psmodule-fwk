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
 * Description of Form
 *
 * @author bruno
 */
class Form
{

    /**
     * Form legend
     * @var array
     */
    private $legend = [];

    /**
     * Form inputs
     * @var array
     */
    private $inputs = [];

    /**
     * Form submit
     * @var array
     */
    private $submit = [];

    /**
     * Form reset
     * @var array
     */
    private $reset;

    /**
     * Default hint for inputs
     * @var string
     */
    private $defaultHint;

    /**
     * Description
     * @var string
     */
    private $description;

    /**
     * Default label for switch true value
     * @var string
     */
    private $defaultSwitchTrue;

    /**
     * Default label for switch true value
     * @var string
     */
    private $defaultSwitchFalse;

    /**
     * Private constructor use getInstance instead
     * @param string $title
     * @param string $submit
     * @param string $reset
     * @param string $defaultHint
     */
    private function __construct($title, $submit, $reset = null, $defaultHint = null)
    {
        $this->legend['title'] = $title;
        $this->submit['title'] = $submit;
        if (isset($reset)) {
            $this->reset['title'] = $reset;
            $this->reset['icon'] = 'process-icon- icon-undo';
        }
        $this->defaultHint = $defaultHint;
    }

    /**
     * Obtain Form instance
     * @param string $title
     * @param string $submit
     * @param string $reset
     * @param string $defaultHint
     * @return Form
     */
    public static function getInstance($title, $submit, $reset = null, $defaultHint = null)
    {
        return new Form($title, $submit, $reset, $defaultHint);
    }

    /**
     * Set the form icon
     * @param string $icon
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->legend['icon'] = $icon;
        return $this;
    }

    /**
     * Add an input
     * @param InputForm $input
     * @return $this
     */
    public function addInput(InputForm $input)
    {
        if (isset($this->defaultHint) && !$input->isPropertySet('hint')) {
            $input->setHint($this->defaultHint);
        }
        if ($input instanceof InputSwitch) {
            if (isset($this->defaultSwitchTrue) && null === $input->getValueLabel(1)) {
                $input->setValueLabel(1, $this->defaultSwitchTrue);
            }
            if (isset($this->defaultSwitchFalse) && null === $input->getValueLabel(0)) {
                $input->setValueLabel(0, $this->defaultSwitchFalse);
            }
        }
        $arrInput = $input->toPrestaShopField();
        if (is_array($arrInput)) {
            $this->inputs[] = $input->toPrestaShopField();
        }
        return $this;
    }

    /**
     * Html name of the submit button
     * @param string $name
     * @return $this
     */
    public function setSubmitName($name)
    {
        $this->submit['name'] = $name;
        $this->submit['id'] = $name;
        return $this;
    }

    /**
     * Set default labels for switch values
     * @param string $trueLabel
     * @param string $falseLabel
     * @return $this
     */
    public function setDefaultSwitchLabels($trueLabel, $falseLabel)
    {
        $this->defaultSwitchTrue = $trueLabel;
        $this->defaultSwitchFalse = $falseLabel;
        return $this;
    }

    /**
     * Set the form description
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Add value in array if it's set
     * @param array $array
     * @param string $key
     * @param mixed $value
     */
    static private function addIfSet(array &$array, $key, $value)
    {
        if (isset($value)) {
            $array[$key] = $value;
        }
    }

    /**
     * To PrestaShop array
     * @return array
     */
    public function toPrestaShopFormat()
    {
        if (empty($this->inputs)) {
            return null;
        }
        $array = [];
        static::addIfSet($array, 'legend', $this->legend);
        static::addIfSet($array, 'input', $this->inputs);
        if (!empty($this->inputs)) {
            static::addIfSet($array, 'submit', $this->submit);
            static::addIfSet($array, 'reset', $this->reset);
        }
        static::addIfSet($array, 'description', $this->description);
        return $array;
    }

}
