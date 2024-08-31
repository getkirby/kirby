<?php

namespace Kirby\Cache;

use Throwable;

/**
 * Cache Value
 * Stores the value, creation timestamp and expiration timestamp
 * and makes it possible to store all three with a single cache key
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Value
{
	/**
	 * Cached value
	 */
	protected mixed $value;

	/**
	 * the number of minutes until the value expires
	 * @todo Rename this property to $expiry to reflect
	 *       both minutes and absolute timestamps
	 */
	protected int $minutes;

	/**
	 * Creation timestamp
	 */
	protected int $created;

	/**
	 * Constructor
	 *
	 * @param int $minutes the number of minutes until the value expires
	 *                     or an absolute UNIX timestamp
	 * @param int|null $created the UNIX timestamp when the value has been created
	 *                          (defaults to the current time)
	 */
	public function __construct($value, int $minutes = 0, int|null $created = null)
	{
		$this->value   = $value;
		$this->minutes = $minutes;
		$this->created = $created ?? time();
	}

	/**
	 * Returns the creation date as UNIX timestamp
	 */
	public function created(): int
	{
		return $this->created;
	}

	/**
	 * Returns the expiration date as UNIX timestamp or
	 * null if the value never expires
	 */
	public function expires(): int|null
	{
		// 0 = keep forever
		if ($this->minutes === 0) {
			return null;
		}

		if ($this->minutes > 1000000000) {
			// absolute timestamp
			return $this->minutes;
		}

		return $this->created + ($this->minutes * 60);
	}

	/**
	 * Creates a value object from an array
	 */
	public static function fromArray(array $array): static
	{
		return new static(
			$array['value'] ?? null,
			$array['minutes'] ?? 0,
			$array['created'] ?? null
		);
	}

	/**
	 * Creates a value object from a JSON string;
	 * returns null on error
	 */
	public static function fromJson(string $json): static|null
	{
		try {
			$array = json_decode($json, true);

			if (is_array($array) === true) {
				return static::fromArray($array);
			}

			return null;
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Converts the object to a JSON string
	 */
	public function toJson(): string
	{
		return json_encode($this->toArray());
	}

	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		return [
			'created' => $this->created,
			'minutes' => $this->minutes,
			'value'   => $this->value,
		];
	}

	/**
	 * Returns the pure value
	 */
	public function value()
	{
		return $this->value;
	}
}
