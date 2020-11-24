<?php

namespace Kirby\Form\Mixin;

trait Max
{
    protected $max;

    public function max(): ?int
    {
        return $this->max;
    }

    protected function setMax(int $max = null)
    {
        $this->max = $max;
    }
}
