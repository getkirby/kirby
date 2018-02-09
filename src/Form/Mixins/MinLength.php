<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\MinLengthException;
use Kirby\Toolkit\V;

trait MinLength
{

    protected $minLength;

    protected function defaultMinLength()
    {
        return null;
    }

    public function minLength()
    {
        return $this->minLength;
    }

    protected function setMinLength(int $minLength = null)
    {
        $this->minLength = $minLength;
        return $this;
    }

    protected function validateMinLength($value)
    {
        if ($this->minLength() !== null) {
            if (V::minLength($value, $this->minLength()) === false) {
                throw new MinLengthException();
            }
        }

        return true;
    }

}
