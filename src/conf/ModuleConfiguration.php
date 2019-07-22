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
     * Quels sont les éléments de configuration ?
     * @return array|ConfElement[]
     */
    abstract protected function getSimpleConfElements();

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
        foreach ($this->getSimpleConfElements() as $element) {
            $ok &= Conf::setValeur($element->getName(), $element->getDefaultValue(), $element->isAllShops());
            if (!$ok) {
                break;
            }
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
    public function uninstall()
    {
        $ok = $this->complementaryUninstall();
        foreach ($this->getSimpleConfElements() as $element) {
            $ok &= Conf::removeValeur($element->getName());
            if (!$ok) {
                break;
            }
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
     * Récupération des inputs pour le formulaire
     * À surcharger pour la plupast des cas
     * @return array|InputForm[]
     */
    protected function getInputs()
    {
        $inputs = [];
        foreach ($this->getSimpleConfElements() as $element) {
            $possibleValues = $element->getPossibleValues();
            if (!empty($possibleValues)) {
                $inputs[] = InputSelect::getInstance($this, $element->getName(), InputSelect::arrayToOptions(array_map(function ($value) {return ['id' => $value, 'value' => $value];}, $possibleValues)));
            } else {
                $inputs[] = InputText::getInstance($this, $element->getName());
            }
        }
        return $inputs;
    }

    /**
     * Affichage du formulaire de configuration
     * @return Form
     */
    public function render()
    {
        $form = Form::getInstance($this->getName(), $this->l('Save'))->setSubmitName($this->getSubmitName())->setIcon($this->getIcon());
        foreach ($this->getInputs() as $input) {
            $form->addInput($input);
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
            foreach ($this->getSimpleConfElements() as $element) {
                $sentValue = Tools::getValue($element->getName(), $element->getDefaultValue());
                $valeursPossible = $element->getPossibleValues();
                if (!empty($valeursPossible) && !in_array($sentValue, $valeursPossible)) {
                    $output .= $this->module->displayError(sprintf($this->l('The value "%s" is forbidden for "%s! Only %s are valid!"'), $sentValue, $element->getName(), implode(', ', $valeursPossible)));
                }
                Conf::setValeur($element->getName(), Tools::getValue($element->getName(), $element->getDefaultValue()), $element->isAllShops());
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
        foreach ($this->getSimpleConfElements() as $element) {
            $form->fields_value[$element->getName()] = Conf::getValeur($element->getName());
        }
    }
}
