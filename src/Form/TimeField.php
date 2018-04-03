<?php

namespace Kirby\Form;

use Kirby\Form\Exceptions\DateException;
use Kirby\Toolkit\V;

class TimeField extends DateField
{

    use Mixins\Time;

    protected function defaultFormat(): string
    {
        return 'H:i:s';
    }

    protected function defaultIcon(): string
    {
        return 'clock';
    }

    protected function defaultLabel(): string
    {
        return 'Time';
    }

    protected function defaultName(): string
    {
        return 'time';
    }

    public function format(): string
    {
        if ($this->format !== null && $this->format !== $this->defaultFormat()) {
            return $this->format;
        }

        return $this->notation() === 24 ? 'H:i' : 'h:i a';
    }

    protected function valueFromInput($value)
    {
        if ($value !== null && $timestamp = strtotime($value)) {
            return date('H:i', $timestamp);
        }

        return null;
    }

    protected function valueToString($value)
    {
        if ($value !== null && $timestamp = strtotime($value)) {
            return date($this->format(), $timestamp);
        }

        return '';
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateTime($value);

        return true;
    }

    protected function validateTime($value): bool
    {
        if ($this->isEmpty($value) === false) {
            if (V::time($value) !== true) {
                throw new DateException();
            }
        }

        return true;
    }


}
