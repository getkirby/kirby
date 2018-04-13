<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

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
        if ($this->isEmpty() === false && $this->minLength() !== null) {
            if (V::minLength($value, $this->minLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.minLength.invalid'
                ]);
            }
        }

        return true;
    }
}
