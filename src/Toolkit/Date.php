<?php

namespace Kirby\Toolkit;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use Kirby\Exception\InvalidArgumentException;

/**
 * Extension for PHP's `DateTime` class
 * @since 3.6.2
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
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
	public function __construct(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	) {
		if (is_int($datetime) === true) {
			$datetime = date('r', $datetime);
		}

		if ($datetime instanceof DateTimeInterface) {
			$datetime = $datetime->format('r');
		}

		parent::__construct($datetime, $timezone);
	}

	/**
	 * Returns the datetime in `YYYY-MM-DD hh:mm:ss` format with timezone
	 */
	public function __toString(): string
	{
		return $this->toString('datetime');
	}

	/**
	 * Rounds the datetime value up to next value of the specified unit
	 *
	 * @param string $unit `year`, `month`, `day`, `hour`, `minute` or `second`
	 * @return $this
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the unit name is invalid
	 */
	public function ceil(string $unit): static
	{
		static::validateUnit($unit);

		$this->floor($unit);
		$this->modify('+1 ' . $unit);
		return $this;
	}

	/**
	 * Returns the interval between the provided and the object's datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function compare(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): DateInterval {
		return $this->diff(new static($datetime, $timezone));
	}

	/**
	 * Gets or sets the day value
	 */
	public function day(int|null $day = null): int
	{
		if ($day === null) {
			return (int)$this->format('d');
		}

		$this->setDate($this->year(), $this->month(), $day);
		return $this->day();
	}

	/**
	 * Rounds the datetime value down to the specified unit
	 *
	 * @param string $unit `year`, `month`, `day`, `hour`, `minute` or `second`
	 * @return $this
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the unit name is invalid
	 */
	public function floor(string $unit): static
	{
		static::validateUnit($unit);

		$formats = [
			'year'   => 'Y-01-01P',
			'month'  => 'Y-m-01P',
			'day'    => 'Y-m-dP',
			'hour'   => 'Y-m-d H:00:00P',
			'minute' => 'Y-m-d H:i:00P',
			'second' => 'Y-m-d H:i:sP'
		];

		$flooredDate = date($formats[$unit], $this->timestamp());
		$this->set($flooredDate);
		return $this;
	}

	/**
	 * Formats the datetime value with a custom handler
	 * or with the globally configured one
	 *
	 * @param 'date'|'intl'|'strftime'|null $handler Custom date handler or `null`
	 *                                               for the globally configured one
	 */
	public function formatWithHandler(
		string|IntlDateFormatter|null $format = null,
		string|null $handler = null
	): string|int|false {
		return Str::date($this->timestamp(), $format, $handler);
	}

	/**
	 * Gets or sets the hour value
	 */
	public function hour(int|null $hour = null): int
	{
		if ($hour === null) {
			return (int)$this->format('H');
		}

		$this->setTime($hour, $this->minute());
		return $this->hour();
	}

	/**
	 * Checks if the object's datetime is the same as the given datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function is(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): bool {
		return $this == new static($datetime, $timezone);
	}

	/**
	 * Checks if the object's datetime is after the given datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function isAfter(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): bool {
		return $this > new static($datetime, $timezone);
	}

	/**
	 * Checks if the object's datetime is before the given datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function isBefore(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): bool {
		return $this < new static($datetime, $timezone);
	}

	/**
	 * Checks if the object's datetime is between the given datetimes
	 */
	public function isBetween(
		string|int|DateTimeInterface $min,
		string|int|DateTimeInterface $max
	): bool {
		return $this->isMin($min) === true && $this->isMax($max) === true;
	}

	/**
	 * Checks if the object's datetime is at or before the given datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function isMax(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): bool {
		return $this <= new static($datetime, $timezone);
	}

	/**
	 * Checks if the object's datetime is at or after the given datetime
	 *
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function isMin(
		string|int|DateTimeInterface $datetime = 'now',
		DateTimeZone|null $timezone = null
	): bool {
		return $this >= new static($datetime, $timezone);
	}

	/**
	 * Gets the microsecond value
	 */
	public function microsecond(): int
	{
		return (int)$this->format('u');
	}

	/**
	 * Gets the millisecond value
	 */
	public function millisecond(): int
	{
		return (int)$this->format('v');
	}

	/**
	 * Gets or sets the minute value
	 */
	public function minute(int|null $minute = null): int
	{
		if ($minute === null) {
			return (int)$this->format('i');
		}

		$this->setTime($this->hour(), $minute);
		return $this->minute();
	}

	/**
	 * Gets or sets the month value
	 */
	public function month(int|null $month = null): int
	{
		if ($month === null) {
			return (int)$this->format('m');
		}

		$this->setDate($this->year(), $month, $this->day());
		return $this->month();
	}

	/**
	 * Returns the datetime which is nearest to the object's datetime
	 *
	 * @param string|int|\DateTimeInterface ...$datetime Datetime strings, UNIX timestamps or objects
	 */
	public function nearest(
		string|int|DateTimeInterface ...$datetime
	): string|int|DateTimeInterface {
		$timestamp = $this->timestamp();
		$minDiff   = PHP_INT_MAX;
		$nearest   = null;

		foreach ($datetime as $item) {
			$itemObject    = new static($item, $this->timezone());
			$itemTimestamp = $itemObject->timestamp();
			$diff          = abs($timestamp - $itemTimestamp);

			if ($diff < $minDiff) {
				$minDiff = $diff;
				$nearest = $item;
			}
		}

		return $nearest;
	}

	/**
	 * Returns an instance of the current datetime
	 *
	 * @param \DateTimeZone|null $timezone
	 */
	public static function now(DateTimeZone|null $timezone = null): static
	{
		return new static('now', $timezone);
	}

	/**
	 * Tries to create an instance from the given string
	 * or fails silently by returning `null` on error
	 */
	public static function optional(
		string|null $datetime = null,
		DateTimeZone|null $timezone = null
	): static|null {
		if (empty($datetime) === true) {
			return null;
		}

		try {
			return new static($datetime, $timezone);
		} catch (Exception) {
			return null;
		}
	}

	/**
	 * Rounds the date to the nearest value of the given unit
	 *
	 * @param string $unit `year`, `month`, `day`, `hour`, `minute` or `second`
	 * @param int $size Rounding step starting at `0` of the specified unit
	 * @return $this
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the unit name or size is invalid
	 */
	public function round(string $unit, int $size = 1): static
	{
		static::validateUnit($unit);

		// round to a step of 1 first
		$floor   = (clone $this)->floor($unit);
		$ceil    = (clone $this)->ceil($unit);
		$nearest = $this->nearest($floor, $ceil);
		$this->set($nearest);

		if ($size === 1) {
			// we are already done
			return $this;
		}

		// validate step size
		if (
			in_array($unit, ['day', 'month', 'year']) && $size !== 1 ||
			$unit === 'hour' && 24 % $size !== 0 ||
			in_array($unit, ['second', 'minute']) && 60 % $size !== 0
		) {
			throw new InvalidArgumentException('Invalid rounding size for ' . $unit);
		}

		// round to other rounding steps
		$value = $this->{$unit}();
		$value = round($value / $size) * $size;
		$this->{$unit}($value);

		return $this;
	}

	/**
	 * Rounds the minutes of the given date
	 * by the defined step
	 * @since 3.7.0
	 *
	 * @param int|array|null $step array of `unit` and `size` to round to nearest
	 */
	public static function roundedTimestamp(
		string|null $date = null,
		int|array|null $step = null
	): int|null {
		if ($date = static::optional($date)) {
			if ($step !== null) {
				$step = static::stepConfig($step, [
					'unit' => 'minute',
					'size' => 1
				]);
				$date->round($step['unit'], $step['size']);
			}

			return $date->timestamp();
		}

		return null;
	}

	/**
	 * Gets or sets the second value
	 */
	public function second(int|null $second = null): int
	{
		if ($second === null) {
			return (int)$this->format('s');
		}

		$this->setTime($this->hour(), $this->minute(), $second);
		return $this->second();
	}

	/**
	 * Overwrites the datetime value with a different one
	 *
	 * @param string|int|\DateTimeInterface $datetime Datetime string, UNIX timestamp or object
	 * @param \DateTimeZone|null $timezone Optional default timezone if `$datetime` is string
	 */
	public function set(
		string|int|DateTimeInterface $datetime,
		DateTimeZone|null $timezone = null
	): void {
		$datetime = new static($datetime, $timezone);
		$this->setTimestamp($datetime->timestamp());
	}

	/**
	 * Normalizes the step configuration array for rounding
	 *
	 * @param array|string|int|null $input Full array with `size` and/or `unit` keys, `unit`
	 *                                     string, `size` int or `null` for the default
	 * @param array|null $default Default values to use if one or both values are not provided
	 */
	public static function stepConfig(
		// no type hint to use InvalidArgumentException at the end
		$input = null,
		array|null $default = ['size' => 1, 'unit' => 'day']
	): array {
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

		throw new InvalidArgumentException('Invalid input');
	}

	/**
	 * Returns the time in `hh:mm:ss` format
	 */
	public function time(): string
	{
		return $this->format('H:i:s');
	}

	/**
	 * Returns the UNIX timestamp
	 */
	public function timestamp(): int
	{
		return $this->getTimestamp();
	}

	/**
	 * Returns the timezone object
	 */
	public function timezone(): DateTimeZone|false
	{
		return $this->getTimezone();
	}

	/**
	 * Returns an instance of the beginning of the current day
	 */
	public static function today(DateTimeZone|null $timezone = null): static
	{
		return new static('today', $timezone);
	}

	/**
	 * Returns the date, time or datetime in `YYYY-MM-DD hh:mm:ss` format
	 * with optional timezone
	 *
	 * @param string $mode `date`, `time` or `datetime`
	 * @param bool $timezone Whether the timezone is printed as well
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the mode is invalid
	 */
	public function toString(
		string $mode = 'datetime',
		bool $timezone = true
	): string {
		$format = match ($mode) {
			'date'     => 'Y-m-d',
			'time'     => 'H:i:s',
			'datetime' => 'Y-m-d H:i:s',
			default    => throw new InvalidArgumentException('Invalid mode')
		};

		if ($timezone === true) {
			$format .= 'P';
		}

		return $this->format($format);
	}

	/**
	 * Gets or sets the year value
	 */
	public function year(int|null $year = null): int
	{
		if ($year === null) {
			return (int)$this->format('Y');
		}

		$this->setDate($year, $this->month(), $this->day());
		return $this->year();
	}

	/**
	 * Ensures that the provided string is a valid unit name
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected static function validateUnit(string $unit): void
	{
		$units = ['year', 'month', 'day', 'hour', 'minute', 'second'];
		if (in_array($unit, $units) === false) {
			throw new InvalidArgumentException('Invalid rounding unit');
		}
	}
}
