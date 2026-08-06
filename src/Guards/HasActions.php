<?php

namespace Kirby\Guards;

use ReflectionMethod;

/**
 * Detects the dedicated action methods that the child
 * classes of an abstract guard class define
 *
 * Every action can get its own protected `ensureTo<Action>()`
 * method. The prefix keeps those methods apart from the
 * shared methods of the base classes, so that helpers such
 * as `error()` or `category()` can never be mistaken for
 * an action.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasActions
{
	/**
	 * Checks if the class defines a dedicated
	 * action method for the given action
	 */
	public function has(string $action): bool
	{
		$method = $this->method($action);

		// action methods are protected, as they are only ever called
		// through `::ensure()`. Private methods are not accessible
		// from the base class and must therefore never count.
		return
			method_exists($this, $method) === true &&
			(new ReflectionMethod($this, $method))->isPrivate() === false;
	}

	/**
	 * Returns the name of the dedicated action
	 * method for the given action
	 */
	protected function method(string $action): string
	{
		return 'ensureTo' . ucfirst($action);
	}
}
