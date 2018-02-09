<?php

namespace Kirby\Form\Mixins;

trait BooleanValue
{

    use Value;

    protected function valueFromInput($value)
    {
        return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
    }

    protected function valueToString($value): string
    {
        return $value === true ? 'true' : 'false';
    }

}
