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

namespace bdesprez\psmodulefwk\conf;

use bdesprez\psmodulefwk\form\InputForm;
use bdesprez\psmodulefwk\helpers\Conf;
use HelperForm;

/**
 * Class ConfElement
 * @package bdesprez\psmodulefwk\conf
 */
class ConfElement
{
    /**
     * Input lié
     * @var InputForm
     */
    private $input;

    /**
     * Valeur par défaut
     * @var mixed
     */
    private $defaultValue;

    /**
     * Pour tous les shops ?
     * @var bool
     */
    private $allShops = true;

    /**
     * ConfElement constructor.
     * @param InputForm $input
     * @param $defaultValue
     * @param bool $allShops
     */
    public function __construct(InputForm $input, $defaultValue, $allShops = true)
    {
        $this->input = $input;
        $this->defaultValue = $defaultValue;
        $this->allShops = $allShops;
    }

    /**
     * @return bool
     */
    public function isAllShops()
    {
        return $this->allShops;
    }

    /**
     * @return InputForm
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Installation = valeur par défaut dans la conf
     * @return bool
     */
    public function install()
    {
        return Conf::setValeur($this->input->getName(), $this->defaultValue, $this->allShops);
    }

    /**
     * Désinstallation = Suppression de la conf
     * @return bool
     */
    public function uninstall()
    {
        return Conf::removeValeur($this->input->getName());
    }

    /**
     * @param HelperForm $form
     */
    public function fillForm(HelperForm $form)
    {
        $this->input->fillForm($form, Conf::getValeur($this->input->getName()));
    }

    /**
     * Enregistrement dans la conf
     */
    public function treatSubmit()
    {
        $submitValue = $this->input->getSubmittedValue($this->defaultValue);
        Conf::setValeur($this->input->getName(), $submitValue, $this->allShops);
    }
}
