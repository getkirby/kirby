<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

trait Max
{
    protected $max;

    protected function defaultMax()
    {
        return null;
    }

    public function max()
    {
        return $this->max;
    }

    protected function setMax(float $max = null)
    {
        $this->max = $max;
        return $this;
    }

    protected function validateMax($value, $message = null): bool
    {
        if ($this->isEmpty() === false && $this->max() !== null) {
            if ($value > $this->max()) {
                throw new InvalidArgumentException([
                    'key' => 'form.max.invalid'
                ]);
            }
        }

        return true;
    }
}
