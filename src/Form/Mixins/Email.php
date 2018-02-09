<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\EmailException;
use Kirby\Toolkit\V;

trait Email
{

    protected function validateEmail($value): bool
    {
        if ($this->isEmpty() === false) {
            if (V::email($value) === false) {
                throw new EmailException();
            }
        }

        return true;
    }

}
