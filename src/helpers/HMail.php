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

use Context;
use Mail;
use Tools;

/**
 * Description of HMail
 *
 * @author bruno
 */
class HMail
{

	/**
	 * Singleton
	 * @var HMail[]
	 */
	private static $instances = [];

    /**
     * Nom du module
     * @var string
     */
	private $moduleName;

    /**
     * Retourne l'instance suivant le module
     * @param $moduleName
     * @return static
     */
	public static function getInstance($moduleName)
	{
		if (!array_key_exists($moduleName, static::$instances))
		{
			static::$instances[$moduleName] = new static($moduleName);
		}
		return static::$instances[$moduleName];
	}

    /**
     * HMail constructor.
     * @param $moduleName
     */
	private function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Envoi d'email via PrestaShop
     * @param string $sujet
     * @param string $destinataire
     * @param string $template
     * @param array $parametres
     * @return boolean
     */
    public function envoyer($sujet, $destinataire, $template, $parametres)
    {
        $context = Context::getContext();
        $template_path = _PS_MODULE_DIR_ . $this->moduleName . DIRECTORY_SEPARATOR . 'mails' . DIRECTORY_SEPARATOR;
        return Mail::Send($context->language->id, $template, Mail::l($sujet), $parametres, $destinataire, null, null, null, null, null, $template_path);
    }

    /**
     * Encodage token
     * @param string $destinataire
     * @param string $template
     * @param string $date
     * @return string
     */
    public function encryptToken($destinataire, $template, $date)
    {
        return Tools::encryptIV($destinataire . $template . $date);
    }

    /**
     * Récupération d'une url à partir d'un controlleur et de paramètres
     * @param string $controller
     * @param array $url_params
     * @return string
     */
    public function getUrlInMessage($controller, $url_params)
    {
        return Context::getContext()->link->getModuleLink($this->moduleName, $controller, $url_params);
    }

}
