<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

trait Length
{
    use MaxLength;
    use MinLength;

    protected function validateLength($value): bool
    {
        return $this->validateMaxLength($value) === true && $this->validateMinLength($value) === true;
    }

}
