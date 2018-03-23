<?php

namespace Kirby\Form\Mixins;

trait Multiline
{

    protected $multiline;

    protected function defaultMultiline(): bool
    {
        return true;
    }

    protected function setMultiline(bool $multiline = null)
    {
        $this->multiline = $multiline;
        return $this;
    }

    public function multiline()
    {
        return $this->multiline;
    }

}
