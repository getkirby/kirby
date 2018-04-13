<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

trait Url
{
    protected function validateUrl($value): bool
    {
        if ($this->isEmpty() === false) {
            if (V::url($value) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.url.invalid'
                ]);
            }
        }

        return true;
    }
}
