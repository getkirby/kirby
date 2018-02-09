<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\MinException;
use Kirby\Toolkit\V;

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
        if ($this->min() !== null) {
            if ($value < $this->min()) {
                throw $message !== null ? new MinException($message) : new MinException();
            }
        }

        return true;
    }

}
