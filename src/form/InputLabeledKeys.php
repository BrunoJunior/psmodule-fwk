<?php

namespace bdesprez\psmodulefwk\form;

use bdesprez\psmodulefwk\ILabeledKeys;

/**
 * Class InputLabeledKeys
 * @package bdesprez\psmodulefwk\form
 */
abstract class InputLabeledKeys extends InputForm
{
    /**
     * Action button
     * @param ILabeledKeys $labeledKeys
     * @param $name
     */
    public function __construct(ILabeledKeys $labeledKeys, $name)
    {
        parent::__construct($name, $labeledKeys->getLabelByKey($name));
    }
}
