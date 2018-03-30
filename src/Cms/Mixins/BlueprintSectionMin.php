<?php

namespace Kirby\Cms\Mixins;

use Kirby\Exception\InvalidArgumentException;

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
            throw new InvalidArgumentException([
                'key'      => 'exception.blueprint.section.min.invalid',
                'fallback' => '400'
            ]);
        }

        $this->min = $min;
        return $this;
    }

    protected function validateMin(): bool
    {
        if ($min = $this->min()) {
            if ($this->total() < $min) {
                throw new InvalidArgumentException([
                    'key'      => 'exception.blueprint.section.min',
                    'fallback' => 'At least {min} entries required',
                    'data'     => ['min' => $min]
                ]);
            }
        }

        return true;
    }


}
