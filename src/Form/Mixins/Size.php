<?php

namespace Kirby\Form\Mixins;

trait Size
{
    protected $size;

    protected function setSize(string $size = null)
    {
        $this->size = $size;
        return $this;
    }

    public function size()
    {
        return $this->size;
    }
}
