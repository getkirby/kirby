<?php

namespace Kirby\Form;

class NumberField extends Field
{
    use Mixins\Help;
    use Mixins\Icon;
    use Mixins\Label;
    use Mixins\MinMax;
    use Mixins\Placeholder;
    use Mixins\Prefix;
    use Mixins\Required;
    use Mixins\Step;
    use Mixins\Value;

    protected function valueFromInput($value)
    {
        return $this->isEmpty($value) === false ? floatval($value) : null;
    }

    protected function validate($value)
    {
        $this->validateRequired($value);
        $this->validateMinMax($value);
        $this->validateStep($value);

        return true;
    }
}
