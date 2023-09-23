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
	)
	{}

	public function resolve(mixed ...$args): mixed
	{
		return call_user_func_array($this->value, $args);
	}
}
