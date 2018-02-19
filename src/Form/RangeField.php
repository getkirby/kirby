<?php

namespace Kirby\Form;

class RangeField extends NumberField
{

    protected $append;
    protected $prepend;

    public function append()
    {
        return $this->append;
    }

    protected function defaultMin()
    {
        return 0;
    }

    protected function defaultMax()
    {
        return 100;
    }

    protected function defaultStep()
    {
        return 1;
    }

    public function prepend()
    {
        return $this->prepend;
    }

    protected function setAppend(string $append = null)
    {
        $this->append = $append;
        return $this;
    }

    protected function setPrepend(string $prepend = null)
    {
        $this->prepend = $prepend;
        return $this;
    }

    protected function setStep(float $step = null)
    {
        $this->step = $step;
        return $this;
    }

    protected function validateStep($value)
    {
        return true;
    }

}
