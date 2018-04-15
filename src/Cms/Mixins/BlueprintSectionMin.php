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
                'key' => 'exception.blueprint.section.min.invalid',
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
                    'key'  => 'blueprint.section.min',
                    'data' => ['min' => $min]
                ]);
            }
        }

        return true;
    }
}
