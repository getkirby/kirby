<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;

/**
 * The session object can be used to
 * store visitor preferences for your
 * site throughout various requests.
 *
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
	 * Alters one or multiple numeric session values by a specified amount
	 *
	 * @param string|array $key The key to adjust or an array with multiple keys
	 * @param int $by Adjustment amount
	 * @param int|null $bound Maximum (when incrementing) or minimum (when decrementing); skipped if null
	 */
	protected function adjust(
		string|array $key,
		int $by = 1,
		int|null $bound = null
	): void {
		// if array passed, call method recursively
		if (is_array($key) === true) {
			foreach ($key as $k) {
				$this->adjust($k, $by, $bound);
			}

			return;
		}

		// make sure we have the correct values before getting
		$this->session->prepareForWriting();

		$value = $this->get($key, 0);

		if (is_int($value) === false) {
			$kind = $by > 0 ? 'increment' : 'decrement';

			throw new LogicException(
				key: 'session.data.' . $kind . '.nonInt',
				data: ['key' => $key],
				fallback: 'Session value "' . $key . '" is not an integer and cannot be ' . $kind . 'ed',
				translate: false
			);
		}

		// adjust the value, but ensure the $bound constraint;
		// don't change a value that's already beyond the bound
		$value = match (true) {
			$bound === null => $value + $by,
			$by > 0         => max($value, min($value + $by, $bound)),
			default         => min($value, max($value + $by, $bound))
		};

		$this->set($key, $value);
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
	 * Decrements one or multiple session values by a specified amount
	 *
	 * @param string|array $key The key to decrement or an array with multiple keys
	 * @param int $by Decrement by which amount?
	 * @param int|null $min Minimum amount (value is not decremented further)
	 */
	public function decrement(
		string|array $key,
		int $by = 1,
		int|null $min = null
	): void {
		if ($by < 0) {
			throw new InvalidArgumentException(
				data: ['method' => 'SessionData::decrement', 'argument' => '$by'],
				translate: false
			);
		}

		$this->adjust($key, $by * -1, $min);
	}

	/**
	 * Returns one or all session values by key
	 *
	 * @param string|null $key The key to get or null for the entire data array
	 * @param mixed $default Optional default value to return if the key is not defined
	 */
	public function get(
		string|null $key = null,
		mixed $default = null
	): mixed {
		if ($key === null) {
			return $this->data;
		}

		return $this->data[$key] ?? $default;
	}

	/**
	 * Increments one or multiple session values by a specified amount
	 *
	 * @param string|array $key The key to increment or an array with multiple keys
	 * @param int $by Increment by which amount?
	 * @param int|null $max Maximum amount (value is not incremented further)
	 */
	public function increment(
		string|array $key,
		int $by = 1,
		int|null $max = null
	): void {
		if ($by < 0) {
			throw new InvalidArgumentException(
				data: ['method' => 'SessionData::increment', 'argument' => '$by'],
				translate: false
			);
		}

		$this->adjust($key, $by, $max);
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
	 * Reloads the data array with the current session data
	 * Only used internally
	 *
	 * @param array $data Currently stored session data
	 */
	public function reload(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * Removes one or multiple session values by key
	 *
	 * @param string|array $key The key to remove or an array with multiple keys
	 */
	public function remove(string|array $key): void
	{
		$this->session->prepareForWriting();

		foreach (A::wrap($key) as $k) {
			unset($this->data[$k]);
		}
	}

	/**
	 * Sets one or multiple session values by key
	 *
	 * @param string|array $key The key to define or a key-value array with multiple values
	 * @param mixed $value The value for the passed key (only if one $key is passed)
	 */
	public function set(
		string|array $key,
		mixed $value = null
	): void {
		$this->session->ensureToken();
		$this->session->prepareForWriting();

		if (is_string($key) === true) {
			$this->data[$key] = $value;
		} else {
			$this->data = array_replace($this->data, $key);
		}
	}
}
