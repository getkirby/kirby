<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\BadMethodCallException;
use Kirby\Toolkit\BlockCollectionAccess;

/**
 * HasMethods
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasMethods
{
	/**
	 * All registered methods
	 */
	public static array $methods = [];

	/**
	 * Calls a registered method class with the
	 * passed arguments
	 *
	 * @throws \Kirby\Exception\BadMethodCallException
	 */
	#[BlockCollectionAccess]
	protected function callMethod(string $method, array $args = []): mixed
	{
		$closure = $this->getMethod($method);

		if ($closure === null) {
			throw new BadMethodCallException(
				message: 'The method ' . $method . ' does not exist'
			);
		}

		return $closure->call($this, ...$args);
	}

	/**
	 * Checks if the object has a registered custom method
	 */
	#[BlockCollectionAccess]
	public function hasMethod(string $method): bool
	{
		return $this->getMethod($method) !== null;
	}

	/**
	 * Returns a registered method by name, either from
	 * the current class or from a parent class ordered by
	 * inheritance order (top to bottom)
	 */
	#[BlockCollectionAccess]
	public function getMethod(string $method): Closure|null
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
