<?php

namespace Kirby\Toolkit;

use DateTime;
use DateTimeZone;
use Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * Extension for PHP's `DateTime` class
 * @since 3.6.2
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Date extends DateTime
{
    /**
     * Class constructor
     *
     * @param string|int|\DateTimeInterface $datetime Datetime string, UNIX timestamp or object
     * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
     */
    public function __construct($datetime = 'now', ?DateTimeZone $timezone = null)
    {
        if (is_int($datetime) === true) {
            $datetime = date('r', $datetime);
        }

        if (is_a($datetime, 'DateTimeInterface') === true) {
            $datetime = $datetime->format('r');
        }

        parent::__construct($datetime, $timezone);
    }

    /**
     * Returns the datetime in ISO 8601 format
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format(static::ISO8601);
    }

    /**
     * Returns the interval between the provided and the object's datetime
     *
     * @param string|int|\DateTimeInterface $datetime
     * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
     * @return \DateInterval
     */
    public function compare($datetime = 'now', ?DateTimeZone $timezone = null)
    {
        return $this->diff(new static($datetime, $timezone));
    }

    /**
     * Gets or sets the day value
     *
     * @param int|null $day
     * @return int
     */
    public function day(?int $day = null): int
    {
        if ($day === null) {
            return (int)$this->format('d');
        }

        $this->setDate($this->year(), $this->month(), $day);
        return $this->day();
    }

    /**
     * Gets or sets the hour value
     *
     * @param int|null $hour
     * @return int
     */
    public function hour(?int $hour = null): int
    {
        if ($hour === null) {
            return (int)$this->format('H');
        }

        $this->setTime($hour, $this->minute());
        return $this->hour();
    }

    /**
     * Checks if the object's datetime is after the given datetime
     *
     * @param string|int|\DateTimeInterface $datetime
     * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
     * @return bool
     */
    public function isAfter($datetime = 'now', ?DateTimeZone $timezone = null): bool
    {
        return $this->compare($datetime, $timezone)->invert === 1;
    }

    /**
     * Checks if the object's datetime is before the given datetime
     *
     * @param string|int|\DateTimeInterface $datetime
     * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
     * @return bool
     */
    public function isBefore($datetime = 'now', ?DateTimeZone $timezone = null): bool
    {
        return $this->compare($datetime, $timezone)->invert === 0;
    }

    /**
     * Checks if the object's datetime is between the given datetimes
     *
     * @param string|int|\DateTimeInterface $after
     * @param string|int|\DateTimeInterface $before
     * @param \DateTimeZone|null $timezone Optional default timezone if `$after`/`$before` are strings
     * @return bool
     */
    public function isBetween($after, $before): bool
    {
        return $this->isAfter($after) === true && $this->isBefore($before) === true;
    }

    /**
     * Gets the microsecond value
     *
     * @return int
     */
    public function microsecond(): int
    {
        return $this->format('u');
    }

    /**
     * Gets the millisecond value
     *
     * @return int
     */
    public function millisecond(): int
    {
        return $this->format('v');
    }

    /**
     * Gets or sets the minute value
     *
     * @param int|null $minute
     * @return int
     */
    public function minute(?int $minute = null): int
    {
        if ($minute === null) {
            return (int)$this->format('i');
        }

        $this->setTime($this->hour(), $minute);
        return $this->minute();
    }

    /**
     * Gets or sets the month value
     *
     * @param int|null $month
     * @return int
     */
    public function month(?int $month = null): int
    {
        if ($month === null) {
            return (int)$this->format('m');
        }

        $this->setDate($this->year(), $month, $this->day());
        return $this->month();
    }

    /**
     * Returns an instance of the current datetime
     *
     * @param \DateTimeZone|null $timezone
     * @return static
     */
    public static function now(?DateTimeZone $timezone = null)
    {
        return new static('now', $timezone);
    }

    /**
     * Tries to create an instance from the given string
     * or fails silently by returning `null` on error
     *
     * @param string|null $datetime
     * @param \DateTimeZone|null $timezone
     * @return static|null
     */
    public static function optional(?string $datetime = null, ?DateTimeZone $timezone = null)
    {
        if ($datetime === null) {
            return null;
        }

        try {
            return new static($datetime, $timezone);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Rounds the date to the nearest value of the given unit
     *
     * @param string $unit `year`, `month`, `day`, `hour`, `minute` or `second`
     * @param int $size Rounding step starting at `0` of the specified unit
     * @return $this
     */
    public function round(string $unit, int $size = 1)
    {
        if (method_exists($this, $unit) === false) {
            throw new InvalidArgumentException('Invalid rounding unit');
        }

        $value = $this->{$unit}();
        $value = round($value / $size) * $size;
        $this->{$unit}($value);

        return $this;
    }

    /**
     * Gets or sets the second value
     *
     * @param int|null $second
     * @return int
     */
    public function second(?int $second = null): int
    {
        if ($second === null) {
            return (int)$this->format('s');
        }

        $this->setTime($this->hour(), $this->minute(), $second);
        return $this->second();
    }

    /**
     * Normalizes the step configuration array for rounding
     *
     * @param array|string|int|null $input Full array with `size` and/or `unit` keys, `unit`
     *                                     string, `size` int or `null` for the default
     * @param array|null $default Default values to use if one or both values are not provided
     * @return array
     */
    public static function stepConfig($input = null, ?array $default = null): array
    {
        $default ??= [
            'size' => 1,
            'unit' => 'day'
        ];

        if ($input === null) {
            return $default;
        }

        if (is_array($input) === true) {
            $input = array_merge($default, $input);
            $input['unit'] = strtolower($input['unit']);
            return $input;
        }

        if (is_int($input) === true) {
            return array_merge($default, ['size' => $input]);
        }

        if (is_string($input) === true) {
            return array_merge($default, ['unit' => strtolower($input)]);
        }
    }

    /**
     * Returns the time in `hh:mm:ss` format
     *
     * @return string
     */
    public function time(): string
    {
        return $this->format('H:i:s');
    }

    /**
     * Returns the UNIX timestamp
     *
     * @return int
     */
    public function timestamp(): int
    {
        return $this->getTimestamp();
    }

    /**
     * Returns the timezone object
     *
     * @return \DateTimeZone
     */
    public function timezone()
    {
        return $this->getTimezone();
    }

    /**
     * Returns an instance of the beginning of the current day
     *
     * @param \DateTimeZone|null $timezone
     * @return static
     */
    public static function today(?DateTimeZone $timezone = null)
    {
        return new static('today', $timezone);
    }

    /**
     * Returns the date, time or datetime in `YYYY-MM-DD hh:mm:ss` format
     * with optional timezone
     *
     * @param string $mode `date`, `time` or `datetime`
     * @param bool $timezone Whether the timezone is printed as well
     * @return string
     */
    public function toString(string $mode = 'datetime', bool $timezone = true): string
    {
        switch ($mode) {
            case 'date':
                $format = 'Y-m-d';
                break;
            case 'time':
                $format = 'H:i:s';
                break;
            case 'datetime':
                $format = 'Y-m-d H:i:s';
                break;
            default:
                throw new InvalidArgumentException('Invalid mode');
        }

        if ($timezone === true) {
            $format .= 'P';
        }

        return $this->format($format);
    }

    /**
     * Gets or sets the year value
     *
     * @param int|null $year
     * @return int
     */
    public function year(?int $year = null): int
    {
        if ($year === null) {
            return (int)$this->format('Y');
        }

        $this->setDate($year, $this->month(), $this->day());
        return $this->year();
    }
}
