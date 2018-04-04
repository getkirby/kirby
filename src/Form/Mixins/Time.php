<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\PropertyException;

trait Time
{

    protected $notation;
    protected $step;

    protected function defaultNotation(): int
    {
        return 24;
    }

    protected function defaultStep(): int
    {
        return 5;
    }

    public function notation()
    {
        return $this->notation;
    }

    protected function setNotation(int $notation = null)
    {
        if (in_array($notation, [12, 24], true) === false) {
            throw new PropertyException('Invalid time notation');
        }

        $this->notation = $notation;
        return $this;
    }

    protected function setStep(int $step = 5)
    {
        $this->step = $step;
        return $this;
    }

    public function step(): int
    {
        return $this->step;
    }

}
