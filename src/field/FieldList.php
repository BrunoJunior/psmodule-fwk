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

use bdesprez\psmodulefwk\ILabeledKeys;

/**
 * Description of FieldList
 *
 * @author bruno
 */
class FieldList
{

    /**
     * List of fields
     * @var Field[]
     */
    private $fields = [];

    /**
     * Add a field to the list
     * Instanciate a new field and return it
     * @param string $name
     * @param string $title
     * @return Field
     */
    public function addNewField($name, $title)
    {
        $field = new Field($name, $title);
        $this->fields[$name] = $field;
        return $field;
    }

    /**
     * Add a field to the list
     * Instanciate a new field and return it
     * @param ILabeledKeys $labeledKeys
     * @param string $name
     * @return Field
     */
    public function addNewLabeledKeyField(ILabeledKeys $labeledKeys, $name)
    {
        $field = new Field($name, $labeledKeys->getLabelByKey($name));
        $this->fields[$name] = $field;
        return $field;
    }

    /**
     * Add a field to the list
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field)
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    /**
     * To PrestaShop Field list array
     * @return array
     */
    public function toPrestashopFieldList()
    {
        $fieldList = [];
        foreach ($this->fields as $key => $field) {
            $fieldList[$key] = $field->toPrestaShopField();
        }
        return $fieldList;
    }

}
