<?php

namespace Kirby\Form\Mixins;

trait Step
{
    protected $step;

    protected function defaultStep()
    {
        return null;
    }

    protected function setStep(float $step = null)
    {
        $this->step = $step !== null ? floatval($step) : null;
        return $this;
    }

    public function step()
    {
        return $this->step;
    }

    protected function validateStep($value)
    {
        return true;
    }
}
