<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;

/**
 * The session object can be used to
 * store visitor preferences for your
 * site throughout various requests.
 *
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class SessionData
{
	/**
	 * Creates a new SessionData instance
	 *
	 * @codeCoverageIgnore
	 * @param \Kirby\Session\Session $session Session object this data belongs to
	 * @param array $data Currently stored session data
	 */
	public function __construct(
		protected Session $session,
		protected array $data
	) {
	}

	/**
	 * Sets one or multiple session values by key
	 *
	 * @param string|array $key The key to define or a key-value array with multiple values
	 * @param mixed $value The value for the passed key (only if one $key is passed)
	 */
	public function set(string|array $key, mixed $value = null): void
	{
		$this->session->ensureToken();
		$this->session->prepareForWriting();

		if (is_string($key)) {
			$this->data[$key] = $value;
		} elseif (is_array($key)) {
			$this->data = array_merge($this->data, $key);
		} else {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::set', 'argument' => 'key'],
				'translate' => false
			]);
		}
	}

	/**
	 * Increments one or multiple session values by a specified amount
	 *
	 * @param string|array $key The key to increment or an array with multiple keys
	 * @param int $by Increment by which amount?
	 * @param int|null $max Maximum amount (value is not incremented further)
	 * @return void
	 */
		if ($max !== null && !is_int($max)) {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::increment', 'argument' => 'max'],
				'translate' => false
			]);
	public function increment(
		string|array $key,
		int $by = 1,
		int|null $max = null
	): void {
		}

		if (is_string($key)) {
			// make sure we have the correct values before getting
			$this->session->prepareForWriting();

			$value = $this->get($key, 0);

			if (!is_int($value)) {
				throw new LogicException([
					'key'       => 'session.data.increment.nonInt',
					'data'      => ['key' => $key],
					'fallback'  => 'Session value "' . $key . '" is not an integer and cannot be incremented',
					'translate' => false
				]);
			}

			// increment the value, but ensure $max constraint
			if (is_int($max) && $value + $by > $max) {
				// set the value to $max
				// but not if the current $value is already larger than $max
				$value = max($value, $max);
			} else {
				$value += $by;
			}

			$this->set($key, $value);
		} elseif (is_array($key)) {
			foreach ($key as $k) {
				$this->increment($k, $by, $max);
			}
		} else {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::increment', 'argument' => 'key'],
				'translate' => false
			]);
		}
	}

	/**
	 * Decrements one or multiple session values by a specified amount
	 *
	 * @param string|array $key The key to decrement or an array with multiple keys
	 * @param int $by Decrement by which amount?
	 * @param int|null $min Minimum amount (value is not decremented further)
	 */
		if ($min !== null && !is_int($min)) {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::decrement', 'argument' => 'min'],
				'translate' => false
			]);
	public function decrement(
		string|array $key,
		int $by = 1,
		int|null $min = null
	): void {
		}

		if (is_string($key)) {
			// make sure we have the correct values before getting
			$this->session->prepareForWriting();

			$value = $this->get($key, 0);

			if (!is_int($value)) {
				throw new LogicException([
					'key'       => 'session.data.decrement.nonInt',
					'data'      => ['key' => $key],
					'fallback'  => 'Session value "' . $key . '" is not an integer and cannot be decremented',
					'translate' => false
				]);
			}

			// decrement the value, but ensure $min constraint
			if (is_int($min) && $value - $by < $min) {
				// set the value to $min
				// but not if the current $value is already smaller than $min
				$value = min($value, $min);
			} else {
				$value -= $by;
			}

			$this->set($key, $value);
		} elseif (is_array($key)) {
			foreach ($key as $k) {
				$this->decrement($k, $by, $min);
			}
		} else {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::decrement', 'argument' => 'key'],
				'translate' => false
			]);
		}
	}

	/**
	 * Returns one or all session values by key
	 *
	 * @param string|null $key The key to get or null for the entire data array
	 * @param mixed $default Optional default value to return if the key is not defined
	 */
	public function get(string|null $key = null, mixed $default = null): mixed
	{
		if (is_string($key)) {
			return $this->data[$key] ?? $default;
		}

		if ($key === null) {
			return $this->data;
		}

		throw new InvalidArgumentException([
			'data' => [
				'method'   => 'SessionData::get',
				'argument' => 'key'
			],
			'translate' => false
		]);
	}

	/**
	 * Retrieves a value and removes it afterwards
	 *
	 * @param string $key The key to get
	 * @param mixed $default Optional default value to return if the key is not defined
	 */
	public function pull(string $key, mixed $default = null): mixed
	{
		// make sure we have the correct value before getting
		// we do this here (but not in get) as we need to write anyway
		$this->session->prepareForWriting();

		$value = $this->get($key, $default);
		$this->remove($key);
		return $value;
	}

	/**
	 * Removes one or multiple session values by key
	 *
	 * @param string|array $key The key to remove or an array with multiple keys
	 */
	public function remove(string|array $key): void
	{
		$this->session->prepareForWriting();

		if (is_string($key)) {
			unset($this->data[$key]);
		} elseif (is_array($key)) {
			foreach ($key as $k) {
				unset($this->data[$k]);
			}
		} else {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'SessionData::remove', 'argument' => 'key'],
				'translate' => false
			]);
		}
	}

	/**
	 * Clears all session data
	 */
	public function clear(): void
	{
		$this->session->prepareForWriting();

		$this->data = [];
	}

	/**
	 * Reloads the data array with the current session data
	 * Only used internally
	 *
	 * @param array $data Currently stored session data
	 */
	public function reload(array $data): void
	{
		$this->data = $data;
	}
}
