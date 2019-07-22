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

use Configuration;
use Tools;

class Conf
{
    /**
     * Permet de récupérer une valeur dans la conf
     * @param string $key
     * @return mixed
     */
    public static function getValeur($key)
    {
        // Suivant le context puis global
        $serializedValue = Configuration::get($key);
        if ($serializedValue === false) {
            $serializedValue = Configuration::getGlobalValue($key);
        }
        if ($serializedValue === false) {
            return $serializedValue;
        }
        // Tente une dé serialisation. Récupère la valeur telle quel en cas d'erreur.
        $value = @unserialize($serializedValue);
        if ($value === false) {
            $value = $serializedValue;
        }
        return $value;
    }

    /**
     * Permet de mettre une valeur dans la conf
     * @param string $key
     * @param mixed $value
     * @param bool $allShops
     * @return boolean
     */
    public static function setValeur($key, $value, $allShops = FALSE)
    {
        $serializedValue = serialize($value);
        if (!$allShops) {
            return Configuration::updateValue($key, $serializedValue);
        }
        Configuration::deleteByName($key);
        return Configuration::updateGlobalValue($key, $serializedValue);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function keyExists($key)
    {
        return Configuration::hasKey($key);
    }

    /**
     * Récupérer la valeur dans le POST ou la valeur actuelle si pas dans le POST
     * @param string $field
     * @return mixed
     */
    public static function getValeurFromPost($field)
    {
        return Tools::getValue($field, static::getValeur($field));
    }

    /**
     * Supprimer une valeur de conf par sa clé
     * @param string $key
     * @return boolean
     */
    public static function removeValeur($key)
    {
        return Configuration::deleteByName($key);
    }
}
