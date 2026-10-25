<?php

namespace Kirby\Toolkit;

use Closure;

/**
 * Store a lazy value (safe from processing inside a closure)
 * in this class wrapper to also protect it from being unwrapped
 * by normal `Closure`/`is_callable()` checks
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @template TValue
 */
class LazyValue
{
	/**
	 * @param Closure(mixed...):TValue $value
	 */
	public function __construct(
		protected Closure $value
	) {
	}

	/**
	 * Resolve the lazy value to its actual value
	 *
	 * @return TValue
	 */
	public function resolve(mixed ...$args): mixed
	{
		return ($this->value)(...$args);
	}

	/**
	 * Unwrap a single value or an array of values
	 */
	public static function unwrap(mixed $data, mixed ...$args): mixed
	{
		if (is_array($data) === true) {
			return A::map(
				$data,
				fn ($value) => static::unwrap($value, ...$args)
			);
		}

		if ($data instanceof static) {
			return $data->resolve(...$args);
		}

		return $data;
	}
}
