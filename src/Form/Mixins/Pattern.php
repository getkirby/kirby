<?php

namespace Kirby\Form\Mixins;

trait Pattern
{

    protected $pattern;

    protected function defaultPattern()
    {
        return null;
    }

    protected function setPattern(string $pattern = null)
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function pattern()
    {
        return $this->pattern;
    }

}
