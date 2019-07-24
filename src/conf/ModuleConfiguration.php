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

use HelperForm;
use Tools;
use bdesprez\psmodulefwk\form\Form;
use bdesprez\psmodulefwk\form\InputForm;
use bdesprez\psmodulefwk\form\InputSelect;
use bdesprez\psmodulefwk\form\InputText;
use bdesprez\psmodulefwk\helpers\Conf;
use bdesprez\psmodulefwk\ILabeledKeys;
use bdesprez\psmodulefwk\MyModule;
use bdesprez\psmodulefwk\TranslationTrait;

/**
 * Class ModuleConfiguration
 * @package bdesprez\psmodulefwk
 */
abstract class ModuleConfiguration implements ILabeledKeys
{
    use TranslationTrait;

    /**
     * @var MyModule
     */
    protected $module;

    /**
     * ConfLabels constructor.
     * @param MyModule $module
     */
    public function __construct(MyModule $module)
    {
        $this->module = $module;
    }

    /**
     * @return MyModule
     */
    final public function getModule()
    {
        return $this->module;
    }

    /**
     * La liste des éléments de la configuration
     * @return array|ConfElement[]
     */
    abstract protected function getElements();

    /**
     * Installation complémentaire en cas de besoin
     * @return bool
     */
    protected function complementaryInstall()
    {
        return true;
    }

    /**
     * @return bool
     */
    final public function install()
    {
        $ok = true;
        $elements = $this->getElements();
        for ($i = 0; $ok && $i < count($elements); $i++) {
            $ok = $elements[$i]->install();
        }
        if ($ok) {
            $ok = $this->complementaryInstall();
        }
        if (!$ok) {
            $this->module->addError($this->l('Configuration install error!'));
        }
        return $ok;
    }

    /**
     * Désinstallation complémentaire en cas de besoin
     * @return bool
     */
    protected function complementaryUninstall()
    {
        return true;
    }

    /**
     * @return bool
     */
    final public function uninstall()
    {
        $ok = $this->complementaryUninstall();
        $elements = $this->getElements();
        for ($i = 0; $ok && $i < count($elements); $i++) {
            $ok = $elements[$i]->uninstall();
        }
        if (!$ok) {
            $this->module->addError($this->l('Configuration uninstall error!'));
        }
        return $ok;
    }

    /**
     * Le nom du bouton d'envoi du formulaire
     * Par défaut submit[nomdelaclasse]
     * @return string
     */
    public function getSubmitName()
    {
        return "submit" . Tools::strtolower(static::getSimpleName());
    }

    /**
     * Le nom de la configuration
     * Utiliser $this->l()
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    protected function getIcon()
    {
        return '';
    }

    /**
     * Affichage du formulaire de configuration
     * @return Form
     */
    public function render()
    {
        $form = Form::getInstance($this->getName(), $this->l('Save'))->setSubmitName($this->getSubmitName())->setIcon($this->getIcon());
        foreach ($this->getElements() as $element) {
            $form->addInput($element->getInput());
        }
        return $form;
    }

    /**
     * @return string
     */
    protected function complementarySubmitTreatment()
    {
        return '';
    }

    /**
     * Traitement des retours de formulaire
     * @return null|string
     */
    public function treatSubmit()
    {
        $output = null;
        if (Tools::isSubmit($this->getSubmitName())) {
            foreach ($this->getElements() as $element) {
                $element->treatSubmit();
            }
            $output .= $this->complementarySubmitTreatment();
        }
        return $output;
    }

    /**
     * Récupération des valeurs actuelles pour les mettre dans le formulaire
     * @param HelperForm $form
     */
    public function fillForm(HelperForm $form)
    {
        foreach ($this->getElements() as $element) {
            $element->fillForm($form);
        }
    }
}
