<?php

namespace Kirby\Template;

/**
 * The slots collection is simplifying
 * slot access. Slots can be access with
 * `$slots->heading()` and accessing a non-existing
 * slot will simply return null.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Slots
{
	/**
	 * Creates a new slots collection
	 * for the given container
	 */
	public function __construct(
		public Container $container,
		public array $slots
	) {
	}

	/**
	 * Magic getter for slots
	 * I.e. `$slots->heading`
	 */
	public function __get(string $name): ?Slot
	{
		return $this->slots[$name] ?? null;
	}

	/**
	 * Magic getter method for slots
	 * I.e. `$slots->heading()`
	 */
	public function __call(string $name, array $args): ?Slot
	{
		return $this->__get($name);
	}
}
