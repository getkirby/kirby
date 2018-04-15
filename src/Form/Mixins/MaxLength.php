<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

trait MaxLength
{
    protected $maxLength;

    protected function defaultMaxLength()
    {
        return null;
    }

    public function maxLength()
    {
        return $this->maxLength;
    }

    protected function setMaxLength(int $maxLength = null)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    protected function validateMaxLength($value)
    {
        if ($this->isEmpty() === false && $this->maxLength() !== null) {
            if (V::maxLength($value, $this->maxLength()) === false) {
                throw new InvalidArgumentException([
                    'key' => 'form.maxLength.invalid'
                ]);
            }
        }

        return true;
    }
}
