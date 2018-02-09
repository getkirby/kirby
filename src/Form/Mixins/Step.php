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
        if ($this->step() !== null && $value % $this->step() !== 0) {
            throw new StepException();
        }

        return true;
    }

}
