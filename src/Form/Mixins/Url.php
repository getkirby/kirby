<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\UrlException;
use Kirby\Toolkit\V;

trait Url
{

    protected function validateUrl($value): bool
    {
        if ($this->isEmpty() === false) {
            if (V::url($value) === false) {
                throw new UrlException();
            }
        }

        return true;
    }

}
