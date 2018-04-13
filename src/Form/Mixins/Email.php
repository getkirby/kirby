<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

trait Email
{

    protected function validateEmail($value): bool
    {
        if ($this->isEmpty() === false) {
            if (V::email($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.email.invalid'
                ]);
            }
        }

        return true;
    }

}
