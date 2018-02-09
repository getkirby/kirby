<?php

namespace Kirby\Form\Mixins;

trait Autofocus
{

    protected $autofocus;

    public function autofocus(): bool
    {
        return $this->autofocus;
    }

    protected function defaultAutofocus(): bool
    {
        return false;
    }

    protected function setAutofocus(bool $autofocus = null)
    {
        $this->autofocus = $autofocus;
        return $this;
    }

}
