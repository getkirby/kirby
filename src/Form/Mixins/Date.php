<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\DateException;
use Kirby\Toolkit\V;

trait Date
{

    protected $format;

    protected function dateFromInput(string $value = null)
    {
        if ($value !== null && $date = strtotime($value)) {
            return date(DATE_W3C, $date);
        }

        return null;
    }

    protected function dateToString($value): string
    {
        if ($date = strtotime($value)) {
            return date($this->format(), $date);
        }

        return '';
    }

    protected function defaultFormat(): string
    {
        return DATE_W3C;
    }

    public function format(): string
    {
        return $this->format;
    }

    protected function setFormat(string $format = null)
    {
        $this->format = $format;
        return $this;
    }

    protected function validateDate($value)
    {
        if ($this->isEmpty($value) === false) {
            if (V::date($value) === false) {
                throw new DateException();
            }
        }
    }

}
