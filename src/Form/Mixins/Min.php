<?php

namespace Kirby\Form\Mixins;

use Kirby\Toolkit\V;

use Kirby\Exception\InvalidArgumentException;

trait Min
{
    protected $min;

    protected function defaultMin()
    {
        return null;
    }

    public function min()
    {
        return $this->min;
    }

    protected function setMin(float $min = null)
    {
        $this->min = $min;
        return $this;
    }

    protected function validateMin($value, $message = null): bool
    {
        if ($this->isEmpty() === false && $this->min() !== null) {
            if ($value < $this->min()) {
                throw $message !== null ? new InvalidArgumentException($message) : new InvalidArgumentException([
                    'key' => 'form.min.invalid'
                ]);
            }
        }

        return true;
    }
}
