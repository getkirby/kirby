<?php

namespace Kirby\Template;

use Countable;

/**
 * The slots collection is simplifying
 * slot access. Slots can be accessed with
 * `$slots->heading()` and accessing a non-existing
 * slot will simply return null.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Slots implements Countable
{
	/**
	 * Creates a new slots collection
	 */
	public function __construct(protected array $slots)
	{
	}

	/**
	 * Magic getter for slots;
	 * e.g. `$slots->heading`
	 */
	public function __get(string $name): Slot|null
	{
		return $this->slots[$name] ?? null;
	}

	/**
	 * Magic getter method for slots;
	 * e.g. `$slots->heading()`
	 */
	public function __call(string $name, array $args): Slot|null
	{
		return $this->__get($name);
	}

	/**
	 * Counts the number of defined slots
	 */
	public function count(): int
	{
		return count($this->slots);
	}
}
