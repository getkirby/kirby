<?php

namespace Kirby\Toolkit;

use DateTime;
use DateTimeZone;
use Exception;
use Kirby\Exception\InvalidArgumentException;

class Date extends DateTime
{

    public function __toString(): string
    {
        return $this->format(static::W3C);
    }

    public static function create(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        return new static($datetime, $timezone);
    }

    public function compare(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        return $this->diff(new static($datetime, $timezone));
    }

    public function day(?int $day = null): int
    {
        if ($day === null) {
            return $this->format('d');
        }

        $this->setDate($this->year(), $this->month(), $day);
        return $this->day();
    }

    public function hour(?int $hour = null): int
    {
        if ($hour === null) {
            return $this->format('H');
        }

        $this->setTime($hour, 0);
        return $this->hour();
    }

    public function isAfter(string $datetime = 'now', ?DateTimeZone $timezone = null): bool
    {
        return $this->compare($datetime, $timezone)->invert === 1;
    }

    public function isBefore(string $datetime = 'now', ?DateTimeZone $timezone = null): bool
    {
        return $this->compare($datetime, $timezone)->invert === 0;
    }

    public function isBetween(string $after, string $before): bool
    {
        return $this->isAfter($after) === true && $this->isBefore($before) === true;
    }

    public function microsecond(): float
    {
        return $this->format('u');
    }

    public function millisecond(): float
    {
        return $this->format('v');
    }

    public function minute(?int $minute = null): int
    {
        if ($minute === null) {
            return $this->format('i');
        }

        $this->setTime($this->hour(), $minute);
        return $this->minute();
    }

    public function month(?int $month = null): int
    {
        if ($month === null) {
            return $this->format('m');
        }

        $this->setDate($this->year(), $month, $this->day());
        return $this->month();
    }

    public static function now(?DateTimeZone $timezone = null)
    {
        return new static('now', $timezone);
    }

    public static function optional(string $date = null, ?DateTimeZone $timezone = null)
    {
        if ($date === null) {
            return null;
        }

        try {
            return new static($date, $timezone);
        } catch (Exception $e) {
            return null;
        }
    }

    public function round($unit, $size = 1)
    {
        if (method_exists($this, $unit) === false) {
            throw new InvalidArgumentException('Invalid rounding unit');
        }

        $value = $this->{$unit}();
        $value = ceil($value / $size) * $size;

        $this->{$unit}($value);
        return $this;
    }

    public function second(?int $second = null): int
    {
        if ($second === null) {
            return $this->format('s');
        }

        $this->setTime($this->hour(), $this->minute(), $second);
        return $this->second();
    }

    public static function step($step = null, ?array $default = null): array
    {
        $default ??= [
            'size' => 1,
            'unit' => 'day'
        ];

        if ($step === null) {
            return $default;
        }

        if (is_array($step) === true) {
            $step = array_merge($default, $step);
            $step['unit'] = strtolower($step['unit']);
            return $step;
        }

        if (is_int($step) === true) {
            return array_merge($default, ['size' => $step]);
        }

        if (is_string($step) === true) {
            return array_merge($default, ['unit' => strtolower($step)]);
        }
    }

    public function time(): string
    {
        return $this->format('H:i:s');
    }

    public function timestamp(): int
    {
        return $this->getTimestamp();
    }

    public function timezone()
    {
        return $this->getTimezone();
    }

    public static function today(?DateTimeZone $timezone = null)
    {
        return new static('today', $timezone);
    }

    public function year(?int $year = null): int
    {
        if ($year === null) {
            return $this->format('Y');
        }

        $this->setDate($year, $this->month(), $this->day());
        return $this->year();
    }

}
