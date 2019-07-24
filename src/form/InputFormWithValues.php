<?php


namespace bdesprez\psmodulefwk\form;


use bdesprez\psmodulefwk\ILabeledKeys;

abstract class InputFormWithValues extends InputLabeledKeys
{
    /**
     * @var array|InputFormValue[]
     */
    private $values = [];

    /**
     * InputFormWithOptions constructor.
     * @param ILabeledKeys $labeledKeys
     * @param $name
     * @param array $values
     */
    public function __construct(ILabeledKeys $labeledKeys, $name, array $values)
    {
        parent::__construct($labeledKeys, $name);
        $this->values = $values;
    }

    /**
     * @return array
     */
    protected function getPrestaShopArrayFormat()
    {
        return [
            'values' => $this->values->toPrestaShopFormat()
        ];
    }
}
