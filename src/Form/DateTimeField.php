<?php

namespace Kirby\Form;

class DateTimeField extends DateField
{
    use Mixins\Time;

    protected function defaultDefault()
    {
        if ($this->required() === true) {
            return 'now';
        }

        return null;
    }
}
