<?php

namespace Kirby\Toolkit;

use Closure;

/**
 * @package   Kirby Toolkit
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class LazyValue
{
	public function __construct(
		protected Closure $value
	) {
	}

	/**
	 * Resolve the lazy value to its actual value
	 */
	public function __invoke(mixed ...$args): mixed
	{
		return call_user_func_array($this->value, $args);
	}

	/**
	 * Unwrap a single value or an array of values
	 */
	public static function unwrap(mixed $data): mixed
	{
		if (is_array($data) === true) {
			return A::map($data, fn ($value) => static::unwrap($value));
		}

		if ($data instanceof static) {
			return $data();
		}

		return $data;
	}
}
