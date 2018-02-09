<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\PropertyException;

trait Time
{

    protected $hours;
    protected $step;

    protected function defaultHours(): string
    {
        return 24;
    }

    protected function defaultStep(): int
    {
        return 60;
    }

    public function hours(): int
    {
        return $this->hours;
    }

    protected function setHours(int $hours = 24)
    {
        if (in_array($hours, [12, 24], true) === false) {
            throw new PropertyException('Invalid time format');
        }

        $this->hours = $hours;
        return $this;
    }

    protected function setStep(int $step = 60)
    {
        $this->step = $step;
        return $this;
    }

    public function step(): int
    {
        return $this->step;
    }

}
