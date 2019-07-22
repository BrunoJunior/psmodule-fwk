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

namespace bdesprez\psmodulefwk\field;

/**
 * Description of Field
 *
 * @author bruno
 */
class Field
{

    /**
     * Name
     * @var string
     */
    private $name;

    /**
     * Title
     * @var string
     */
    private $title;

    /**
     * Text align
     * @var string
     */
    private $align;

    /**
     * Collumn width
     * @var int
     */
    private $width;

    /**
     * Data type
     * @var string
     */
    private $type;

    /**
     * Callback name
     * @var string
     */
    private $callback;

    /**
     * Callback object
     * @var object
     */
    private $callbackObject;

    /**
     * Filter key
     * @var string
     */
    private $filterKey;

    /**
     * Filter type
     * @var string
     */
    private $filterType;

    /**
     * Select list
     * @var array
     */
    private $list;

    /**
     * Collumn class
     * @var string
     */
    private $class;

    /**
     * Column have filter
     * @var boolean
     */
    private $havingFilter;

    /**
     * Color
     * @var string
     */
    private $color;

    /**
     * Order key
     * @var string
     */
    private $orderKey;

    /**
     * Order by enabled
     * @var boolean
     */
    private $orderBy;

    /**
     * Temp table filter
     * @var boolean
     */
    private $tmpTableFilter;

    /**
     * Have search field
     * @var boolean
     */
    private $search;

    /**
     *
     * @param string $name
     * @param string $title
     */
    public function __construct($name, $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    /**
     * Select type
     * @param array $list
     * @return $this
     */
    public function setTypeSelect(array $list)
    {
        $this->type = 'select';
        $this->list = $list;
        return $this;
    }

    /**
     * Simple type
     * @param string $type
     * @return $this
     */
    private function setSimpleType($type)
    {
        $this->type = $type;
        $this->list = NULL;
        return $this;
    }

    /**
     * Bool type
     * @return $this
     */
    public function setTypeBool()
    {
        return $this->setSimpleType('bool');
    }

    /**
     * Text type
     * @return $this
     */
    public function setTypeText()
    {
        return $this->setSimpleType('text');
    }

    /**
     * Date type
     * @return $this
     */
    public function setTypeDate()
    {
        return $this->setSimpleType('dbte');
    }

    /**
     * Datetime type
     * @return $this
     */
    public function setTypeDatetime()
    {
        return $this->setSimpleType('datetime');
    }

    /**
     * Align to right
     * @return $this
     */
    public function alignRight()
    {
        $this->align = 'text-right';
        return $this;
    }

    /**
     * Align to center
     * @return $this
     */
    public function alignCenter()
    {
        $this->align = 'center';
        return $this;
    }

    /**
     * Align to left
     * @return $this
     */
    public function alignLeft()
    {
        $this->align = NULL;
        return $this;
    }

    /**
     * Define width
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Define callback
     * @param string $callback
     * @param object $object
     * @return $this
     */
    public function setCallback($callback, $object = null)
    {
        $this->callback = $callback;
        $this->callbackObject = $object;
        return $this;
    }

    /**
     * Define filter key
     * @param string $filterKey
     * @return $this
     */
    public function setFilterKey($filterKey)
    {
        $this->filterKey = $filterKey;
        return $this;
    }

    /**
     * Define filter type
     * @param string $filterType
     * @return $this
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
        return $this;
    }

    /**
     * Define css class
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * If set to true, the WHERE clause used to filter results will use the $_filterHaving variable (optional, default false).
     * @param boolean $havingFilter
     * @return $this
     */
    public function setHavingFilter($havingFilter = true)
    {
        $this->havingFilter = $havingFilter;
        return $this;
    }

    /**
     * Have search
     * @param boolean $search
     * @return $this
     */
    public function setSearch($search = true)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Color
     * @param string $color
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Enable/disable order by
     * @param boolean $orderBy
     * @return $this
     */
    public function setOrderBy($orderBy = true)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * Order key
     * @param string $orderKey
     * @return $this
     */
    public function setOrderKey($orderKey)
    {
        $this->orderKey = $orderKey;
        return $this;
    }

    /**
     * tmpTableFilter
     * @param string $tmpTableFilter
     * @return $this
     */
    public function setTmpTableFilter($tmpTableFilter)
    {
        $this->tmpTableFilter = $tmpTableFilter;
        return $this;
    }

    /**
     * Field name
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function toPrestaShopField()
    {
        $array = [];
        static::addIfSet($array, 'title', $this->title);
        static::addIfSet($array, 'align', $this->align);
        static::addIfSet($array, 'width', $this->width);
        static::addIfSet($array, 'callback', $this->callback);
        static::addIfSet($array, 'callback_object', $this->callbackObject);
        static::addIfSet($array, 'class', $this->class);
        static::addIfSet($array, 'type', $this->type);
        static::addIfSet($array, 'list', $this->list);
        static::addIfSet($array, 'havingFilter', $this->havingFilter);
        static::addIfSet($array, 'filter_key', $this->filterKey);
        static::addIfSet($array, 'filter_type', $this->filterType);
        static::addIfSet($array, 'color', $this->color);
        static::addIfSet($array, 'order_key', $this->orderKey);
        static::addIfSet($array, 'orderby', $this->orderBy);
        static::addIfSet($array, 'tmpTableFilter', $this->tmpTableFilter);
        static::addIfSet($array, 'search', $this->search);
        return $array;
    }

}
