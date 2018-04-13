<?php

namespace Kirby\Form\Mixins;

trait Prefix
{
    protected $prefix;

    public function prefix()
    {
        return $this->prefix;
    }

    protected function setPrefix(string $prefix = null)
    {
        $this->prefix = $prefix;
        return $this;
    }
}
