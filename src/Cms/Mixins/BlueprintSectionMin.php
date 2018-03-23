<?php

namespace Kirby\Cms\Mixins;

use Exception;

trait BlueprintSectionMin
{

    protected $min;

    public function min()
    {
        return $this->min;
    }

    protected function setMin(int $min = null)
    {
        if ($min !== null && $min < 1) {
            throw new Exception('The min value must be 1 or higher');
        }

        $this->min = $min;
        return $this;
    }

    protected function validateMin(): bool
    {
        if ($min = $this->min()) {
            if ($this->total() < $min) {
                throw new Exception('At least ' . $min . ' entries required');
            }
        }

        return true;
    }


}
