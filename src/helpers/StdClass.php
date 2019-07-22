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

namespace bdesprez\psmodulefwk\helpers;

use JsonSerializable;

/**
 * Description of HStdClass
 *
 * @author bruno
 */
class StdClass implements JsonSerializable {

    /**
     * Valeur
     * @var mixed
     */
    private $valeur;

    /**
     * Constructeur
     * @param mixed $valeur
     */
    public function __construct($valeur) {
        $this->valeur = $valeur;
    }

    /**
     * Magic getter
     * @param $name
     * @return static
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Magic setter
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Offre la possibilité de chaîner plus proprement
     * @param mixed $valeur
     * @return StdClass
     */
    public static function getInstance($valeur) {
        return new StdClass($valeur);
    }

    /**
     * Obtenir un élément enfant
     * @param string $attribut
     * @param mixed $defaut
     * @return StdClass
     */
    public function get($attribut, $defaut = null) {
        $valeur = null;
        if ($this->valeur !== null && is_object($this->valeur) && property_exists($this->valeur, $attribut)) {
            $valeur = $this->valeur->$attribut;
        }
        if ($valeur === null) {
            $valeur = $defaut;
        }
        return new StdClass($valeur);
    }

    /**
     * Obtenir la valeur de l'élément enfant
     * @param string $attribut
     * @param mixed $defaut
     * @return mixed
     */
    public function getValeur($attribut, $defaut = null) {
        //#11345
        return $this->get($attribut, $defaut)->valeur();
    }

    /**
     * Mettre la valeur dans $this->valeur si cette dernière est un objet
     * @param string $attribut
     * @param mixed $valeur
     * @return StdClass
     */
    public function set($attribut, $valeur) {
        if ($this->isObject()) {
            $this->valeur->$attribut = $valeur;
        }
        return $this->get($attribut);
    }

    /**
     * Pas d'attribut : La valeur est-elle un objet ?
     * Attribut : La valeur de l'attribut de $this est-elle un objet ?
     * @param string $attribut
     * @return boolean
     */
    public function isObject($attribut = null) {
        if (!is_string($attribut)) {
            return is_object($this->valeur());
        }
        return $this->get($attribut)->isObject();
    }

    /**
     * La valeur est-elle définie ?
     * @param string $attribut
     * @return boolean
     */
    public function isDefined($attribut = null) {
        if (!is_string($attribut)) {
            $valeur = $this->valeur();
            return isset($valeur);
        }
        return $this->get($attribut)->isDefined();
    }

    /**
     * La valeur est-elle vide ?
     * @param string $attribut
     * @return boolean
     */
    public function isEmpty($attribut = null) {
        if (!is_string($attribut)) {
            $valeur = $this->valeur();
            return empty($valeur);
        }
        return $this->get($attribut)->isEmpty();
    }

    /**
     * La valeur est-elle un objet avec cet attribut
     * @param string $attribut
     * @return boolean
     */
    public function hasAttribut($attribut) {
        if (!$this->isObject()) {
            return false;
        }
        return property_exists($this->valeur(), $attribut);
    }

    /**
     * Retourne la valeur
     * @return mixed
     */
    public function valeur() {
        return $this->valeur;
    }

    /**
     * Vérification de l'égalité
     * @param mixed $valeur
     * @param string $attribut
     * @param boolean $strict
     * @return boolean
     */
    public function isEqual($valeur, $attribut = null, $strict = true) {
        if (is_string($attribut)) {
            return $this->get($attribut)->isEqual($valeur, null, $strict);
        }
        if ($valeur instanceof StdClass) {
            $valeur = $valeur->valeur();
        }
        if ($strict) {
            return $this->valeur() === $valeur;
        } else {
            return $this->valeur() == $valeur;
        }
    }

    /**
     * Serialisation de la valeur
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->valeur();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->valeur;
    }

    /**
     * Vérifier que l'objet n'a pas d'autres propriétés que celles définies en paramètres
     * @param array $properties
     * @return bool
     */
    public function hasOnlyProperties(array $properties) {
        if (!$this->isObject()) {
            return false;
        }
        $champsAutorises = [];
        $thisProps = array_keys(get_object_vars($this->valeur));
        foreach ($properties as $key => $value) {
            if (is_string($key)) {
                $subProps = is_array($value) ? $value : [$value];
                if (!$this->get($key)->hasOnlyProperties($subProps)) {
                    return false;
                }
                $champsAutorises[] = $key;
            } else {
                $champsAutorises[] = $value;
            }
        }
        $champsNonAutorises = array_diff($thisProps, $champsAutorises);
        return empty($champsNonAutorises);
    }

}
