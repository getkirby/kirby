<?php

namespace Kirby\Form\Field;

use Kirby\Form\Field;

class Radio extends Field
{

    protected function inputClassName()
    {
        return 'Kirby\\Form\\Input\\Radios';
    }

}
