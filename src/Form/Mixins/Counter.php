<?php

namespace Kirby\Form\Mixins;

trait Counter
{

    protected $counter;

    public function counter(): bool
    {
        // automatically switch on the counter if maxLength is set
        if (method_exists($this, 'maxLength') && $this->maxLength() !== null) {
            return true;
        }

        // automatically switch on the counter if minLength is set
        if (method_exists($this, 'minLength') && $this->minLength() !== null) {
            return true;
        }

        return $this->counter;
    }

    protected function defaultCounter(): bool
    {
        return false;
    }

    protected function setCounter(bool $counter = false)
    {
        $this->counter = $counter;
        return $this;
    }

}
