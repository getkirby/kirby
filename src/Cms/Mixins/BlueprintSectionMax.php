<?php

namespace Kirby\Cms\Mixins;

use Kirby\Exception\InvalidArgumentException;

trait BlueprintSectionMax
{
    protected $max;

    public function max()
    {
        return $this->max;
    }

    protected function setMax(int $max = null)
    {
        $this->max = $max;
        return $this;
    }

    protected function validateMax(): bool
    {
        if ($max = $this->max()) {
            if ($this->total() > $max) {
                throw new InvalidArgumentException([
                    'key'  => 'blueprint.section.max',
                    'data' => ['max' => $max]
                ]);
            }
        }

        return true;
    }
}
