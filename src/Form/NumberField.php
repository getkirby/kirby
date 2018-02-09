<?php

namespace Kirby\Form;

class NumberField extends Field
{

    use Mixins\MinMax;
    use Mixins\Required;
    use Mixins\Step;
    use Mixins\Value;

    protected function valueFromInput($value)
    {
        return $value !== null ? floatval($value) : null;
    }

    protected function validate($value)
    {
        $this->validateRequired($value);
        $this->validateMinMax($value);
        $this->validateStep($value);

        return true;
    }

}
