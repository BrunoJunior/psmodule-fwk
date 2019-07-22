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

use Tools;

/**
 * Classe utilitaire
 *
 * @author bruno
 */
class Stringer {

    const VAL_DEFAUT_NA = 'A DEFINIR';
    const VAL_DEFAUT_VIDE = ' ';

    private static function getXMLCaracteresEntites() {
        return array(array('&', '>', '<', '"', "'"), array('&amp;', '&gt;', '&lt;', '&quot;', '&apos;'));
    }

    /**
     * Permet d'échapper les caractères spécifiques au XML
     * @param string $chaine
     * @return string
     */
    public static function encodeXML($chaine) {
        $xml_car_ent = static::getXMLCaracteresEntites();
        return str_replace($xml_car_ent[0], $xml_car_ent[1], $chaine);
    }

    /**
     * Permet de décoder une chaine XML encodée
     * @param string $chaine
     * @return string
     */
    public static function decodeXML($chaine) {
        $xml_car_ent = static::getXMLCaracteresEntites();
        return str_replace($xml_car_ent[1], $xml_car_ent[0], $chaine);
    }

    /**
     * Utile pour "trimer" les éléments d'un tableau
     * @param mixed $value : La valeur à traiter.
     */
    public static function appliquerTrim(&$value) {
        $value = trim($value);
    }

    /**
     * enlève les espaces superflus ainsi que les tabulations et les retours chariot
     * @param string $chaine
     * @return string
     */
    private static function trimXL($chaine) {
        return preg_replace('( +)', ' ', str_replace("\n", ' ', str_replace("\r", ' ', str_replace("\t", ' ', trim($chaine)))));
    }

    /**
     * On enlève les accents d'une chaine
     * @param string $chaine
     * @return string
     */
    private static function sansAccent($chaine) {
        $t_string = trim(mb_strtolower(html_entity_decode($chaine, ENT_QUOTES)));
        $accent = array('à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð'
            , 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ü', 'ù', 'ú', 'û', 'ý', 'ý', 'þ', 'ÿ');
        $noaccent = array('a', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o'
            , 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'b', 'y');

        return str_replace($accent, $noaccent, $t_string);
    }

    /**
     * Remplace les caractères spéciaux par des espaces
     * @param string $chaine
     * @return string
     */
    private static function caracteresSpeciaux($chaine) {
        $t_string = trim(mb_strtolower(html_entity_decode($chaine, ENT_QUOTES)));
        $spec = array("'", '-', '%', '&', ',', ';', ':', '.', '_', '(', ')', '@', '|', '_', '"', '#', '\\'
            , '/', '*', '+', '=', '}', '!', '{', '[', ']', '?', '°', '€');
        $nospec = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '
            , ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');

        return static::trimXL(preg_replace('`( | |Â|&nbsp;)+`i', ' ', (str_replace($spec, $nospec, $t_string))));
    }

    /**
     * Remplacer les caractères spéciaux pour le titre de l'article
     * @param string|array $chaine
     * @return string|array
     */
    public static function remplacerPourTitre($chaine) {
        $tbl_remplacement = [
            // Fractions
            '¼' => '1/4', '½' => '1/2', '¾' => '3/4', '⅓' => '1/3', '⅔' => '2/3',
            // Caractères interdits
            '<' => '', '>' => '', ';' => '', '#' => '', '{' => '', '}' => '', '~' => '', '`' => '', '^' => '', '¨' => '',
            // Considérés comme espace
            '|' => ' ',
            // Autres
            '´' => '\'', '=' => ':'
        ];

        if (is_array($chaine)) {
            $arrChaine = [];
            foreach ($chaine as $key => $value) {
                $arrChaine[$key] = static::remplacerPourTitre($value);
            }
            return $arrChaine;
        }

        foreach ($tbl_remplacement as $from => $to) {
            $chaine = str_replace($from, $to, $chaine);
        }
        return trim($chaine);
    }

    /**
     * URL rewriting
     * @param string $value
     * @return string
     */
    public static function genNomUrl($value) {
        $t_string = trim(strtolower(static::caracteresSpeciaux(static::sansAccent($value))), '-');
        return $t_string;
    }

    /**
     * CamelCase to underscore_case conversion
     * @param string $str_camel
     * @param string $separator
     * @return string
     */
    public static function uncamel($str_camel, $separator = '_') {
        if (empty($separator)) {
            return $str_camel;
        }
        $str_camel[0] = strtolower($str_camel[0]);
        return preg_replace('/([A-Z])/', "'$separator' . strtolower('$1')", $str_camel);
    }

    /**
     * underscore_case to CamelCase conversion
     * @param string $str_underscore
     * @param string $separator
     * @return string
     */
    public static function camel($str_underscore, $separator = '_') {
        if (empty($separator)) {
            return $str_underscore;
        }
        $words = str_replace($separator, ' ', $str_underscore);
        return str_replace(' ', '', ucwords($words));
    }

    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    /**
     * Replace the last occurence
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    static public function str_lreplace($search, $replace, $subject) {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * Entourer une chaîne par une balise <p></p>
     * @param string $chaine
     * @return string
     */
    static public function entourerParagrapheHtml($chaine) {
        // On ne fait rien si la chaîne est déjà entourée par une div ou un paragraphe
        if (Tools::substr($chaine, 0, 3) === '<p>' || Tools::substr($chaine, 0, 5) === '<div>') {
            return $chaine;
        }
        return "<p>{$chaine}</p>";
    }

}
