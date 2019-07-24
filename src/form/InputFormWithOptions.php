<?php


namespace bdesprez\psmodulefwk\form;

use bdesprez\psmodulefwk\ILabeledKeys;
use HelperForm;

abstract class InputFormWithOptions extends InputLabeledKeys
{
    /**
     * @var InputFormOptions
     */
    private $options;

    /**
     * InputFormWithOptions constructor.
     * @param ILabeledKeys $labeledKeys
     * @param $name
     * @param InputFormOptions $options
     */
    public function __construct(ILabeledKeys $labeledKeys, $name, InputFormOptions $options)
    {
        parent::__construct($labeledKeys, $name);
        $this->options = $options;
    }

    /**
     * @return string
     */
    protected function getOptionsKeyInPsFormat()
    {
        return 'options';
    }

    /**
     * @return InputFormOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    protected function getPrestaShopArrayFormat()
    {
        return [
            $this->getOptionsKeyInPsFormat() => $this->options->toPrestaShopFormat()
        ];
    }

    /**
     * @param array $default
     * @return mixed
     */
    public function getSubmittedValue($default = [])
    {
        return array_filter(array_keys($this->getOptions()->getValues()), function ($id) {
            return \Tools::getValue($this->getName() . '_' . $id);
        });
    }

    /**
     * @param HelperForm $form
     * @param $value
     * @return self
     */
    public function fillForm(HelperForm $form, $value)
    {
        foreach (array_keys($this->getOptions()->getValues()) as $id) {
            $form->fields_value[$this->getName() . '_' . $id] = in_array($id, $value);
        }
        return $this;
    }
}
