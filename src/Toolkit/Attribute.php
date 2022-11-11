<?php

namespace Kirby\Toolkit;

/**
 * The `Attribute` class represents the value of an HTML attributes
 * and provides various utilities for merging and joinging attribute
 * values.
 *
 * @package   Kirby Toolkit
 * @author    Fabian Michael <hallo@fabianmichael.de>
 * @link      https://fabianmichael.de
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Attribute
{
	protected mixed $value;
	protected bool $prepends = false;

	protected function __construct(mixed $value, bool|null $prepends = null)
	{
		if (is_a($value, static::class)) {
			// If an instance of this class is passed as value,
			// extract all informartion and transfer to this instance.
			$this->value = $value->value();
			$prepends = $value->prepends;
		} else {
			$this->value = $value;
		}

		$this->prepends = $prepends ?? false;
	}

	/**
	 * Creates a new instance from an arbitrary value. Passing an
	 * instance of this class to this method will return that
	 * instance instead.
	 */
	public static function from(mixed $value = null): static
	{
		return new static($value, false);
	}

	/**
	 * Creates an instance of this class, whose value will be merged
	 * instead of repleat
	 */
	public static function prepends(mixed $value = null): static
	{
		return new static($value, true);
	}

	/**
	 * If an attribute is merged with a new value, this will either
	 * return a new instance with the new value
	 */
	public function merge(mixed $attribute): static
	{
		if ($this->prepends) {
			$append = (string) $attribute;

			if (!empty($append) && strlen($append) > 0) {
				return static::prepends($this->value . r(!empty($this->value), ' ') . $append);
			}
		}

		return new static($attribute);
	}

	/**
	 * Returns the raw value.
	 */
	public function value(): mixed
	{
		return $this->value;
	}

	public function __toString()
	{
		return (string) $this->value;
	}
}
