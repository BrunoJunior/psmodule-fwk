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
 * Description of InputForm
 *
 * @author bruno
 */
abstract class InputForm
{

    const TYPE_TEXT = 'text';
    const TYPE_SELECT = 'select';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_FILE = 'file';
    const TYPE_SHOP = 'shop';
    const TYPE_ASSO_SHOP = 'asso_shop';
    const TYPE_FREE = 'free';
    const TYPE_COLOR = 'color';
    const TYPE_SWITCH = 'switch';
    const TYPE_DATE = 'date';
    const TYPE_ACTION = 'action';
    const TYPE_MAPPING = 'mapping';

    /**
     * Theoretically optional, but in reality each field has to have a label
     * @var string
     */
    private $label;

    /**
     * The name of the object property from which we get the value
     * @var string
     */
    private $name;

    /**
     * If true, PrestaShop will add a red star next to the field
     * @var boolean
     */
    private $required;

    /**
     * Description displayed under the field
     * @var string
     */
    private $desc;

    /**
     * This is displayed when the mouse hovers the field
     * @var string
     */
    private $hint;

    /**
     * This is displayed after the field (ie. to indicate the unit of measure)
     * @var string
     */
    private $suffix;

    /**
     * To be displayed when the field is empty
     * @var string
     */
    private $emptyMessage;

    /**
     * Is the field multilang ?
     * @var bool
     */
    private $lang;

    /**
     * Input size
     * @var integer
     */
    private $size;

    /**
     * Generate input on show ?
     * @var boolean
     */
    private $show = true;

    /**
     * Read only input
     * @var boolean
     */
    private $readOnly;

    /**
     * Private constructor use get[Type]Instance instead
     * @param string $name
     * @param string $label
     */
    protected function __construct($name, $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * Is the input required ?
     * If true, PrestaShop will add a red star next to the field
     * @param boolean $required
     * @return $this
     */
    public function isRequired($required = true)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Description displayed under the field
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->desc = $description;
        return $this;
    }

    /**
     * This is displayed when the mouse hovers the field
     * @param string $hint
     * @return $this
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * This is displayed after the field (ie. to indicate the unit of measure)
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * To be displayed when the field is empty
     * @param string $message
     * @return $this
     */
    public function setEmptyMessage($message)
    {
        $this->emptyMessage = $message;
        return $this;
    }

    /**
     * Input size
     * @param integer $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Is the field multilang?
     * @param boolean $lang
     * @return $this
     */
    public function isLang($lang = true)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Is the field generated?
     * @param boolean $show
     * @return $this
     */
    public function isShow($show = true)
    {
        $this->show = $show;
        return $this;
    }

    /**
     * Read only input?
     * @param boolean $readOnly
     * @return $this
     */
    public function isReadOnly($readOnly = true)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * Is a property set?
     * @param string $property
     * @return boolean
     */
    public function isPropertySet($property)
    {
        return isset($this->$property);
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
     *
     * @return array
     */
    protected function getPrestaShopArrayFormat()
    {
        return [];
    }

    /**
     * Input Form type
     * @return string
     */
    abstract protected function getType();

    /**
     * To PrestaShop array
     * @return array
     */
    final public function toPrestaShopField()
    {
        if (!$this->show) {
            return null;
        }
        $array = [];
        foreach ($this->getPrestaShopArrayFormat() as $key => $value) {
            static::addIfSet($array, $key, $value);
        }
        static::addIfSet($array, 'type', $this->getType());
        static::addIfSet($array, 'label', $this->label);
        static::addIfSet($array, 'name', $this->name);
        static::addIfSet($array, 'required', $this->required);
        static::addIfSet($array, 'desc', $this->desc);
        static::addIfSet($array, 'hint', $this->hint);
        static::addIfSet($array, 'suffix', $this->suffix);
        static::addIfSet($array, 'empty_message', $this->emptyMessage);
        static::addIfSet($array, 'lang', $this->lang);
        static::addIfSet($array, 'size', $this->size);
        static::addIfSet($array, 'readonly', $this->readOnly);
        if ($this->readOnly) {
            $array['disabled'] = "disabled";
        }
        return $array;
    }

}
