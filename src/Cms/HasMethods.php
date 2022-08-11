<?php

namespace Kirby\Cms;

use Kirby\Exception\BadMethodCallException;

/**
 * HasMethods
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasMethods
{
	/**
	 * All registered methods
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * Calls a registered method class with the
	 * passed arguments
	 *
	 * @internal
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws \Kirby\Exception\BadMethodCallException
	 */
	public function callMethod(string $method, array $args = [])
	{
		$closure = $this->getMethod($method);

		if ($closure === null) {
			throw new BadMethodCallException('The method ' . $method . ' does not exist');
		}

		return $closure->call($this, ...$args);
	}

	/**
	 * Checks if the object has a registered method
	 *
	 * @internal
	 * @param string $method
	 * @return bool
	 */
	public function hasMethod(string $method): bool
	{
		return $this->getMethod($method) !== null;
	}

	/**
	 * Returns a registered method by name, either from
	 * the current class or from a parent class ordered by
	 * inheritance order (top to bottom)
	 *
	 * @param string $method
	 * @return \Closure|null
	 */
	protected function getMethod(string $method)
	{
		if (isset(static::$methods[$method]) === true) {
			return static::$methods[$method];
		}

		foreach (class_parents($this) as $parent) {
			if (isset($parent::$methods[$method]) === true) {
				return $parent::$methods[$method];
			}
		}

		return null;
	}
}
