<?php

namespace Kirby\Toolkit;

/**
 * Laravel-style static facades
 * for class instances
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Facade
{
	/**
	 * Returns the instance that should be
	 * available statically
	 */
	abstract public static function instance();

	/**
	 * Proxy for all public instance calls
	 */
	public static function __callStatic(string $method, array $args = null)
	{
		return static::instance()->$method(...$args);
	}
}
