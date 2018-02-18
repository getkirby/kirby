<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\StepException;

trait Step
{

    protected $step;

    protected function defaultStep()
    {
        return null;
    }

    protected function setStep(float $step = null)
    {
        $this->step = $step;
        return $this;
    }

    public function step()
    {
        return $this->step;
    }

    protected function validateStep($value)
    {
        // TODO: Find way that works with floats as well
        // if ($this->step() !== null && $value % $this->step() !== 0) {
        if ($this->step() !== null) {
            throw new StepException();
        }

        return true;
    }

}
