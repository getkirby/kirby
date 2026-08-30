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
 * Action names are matched case-sensitively, just like the
 * permission rules in a role or blueprint.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
trait HasActions
{
	/**
	 * Cache of the resolved action methods per class and action.
	 * The lookup only depends on the class, never on the model or
	 * the user, so it can be shared between all instances and
	 * survives for the whole request.
	 */
	protected static array $actions = [];

	/**
	 * Returns the name of the action for the given
	 * dedicated action method
	 */
	protected function action(string $method): string
	{
		return lcfirst(substr($method, strlen('ensureTo')));
	}

	/**
	 * Checks if the class defines a dedicated
	 * action method for the given action
	 */
	public function has(string $action): bool
	{
		return static::$actions[static::class][$action] ??= $this->hasAction($action);
	}

	/**
	 * Resolves whether the class defines a dedicated
	 * action method for the given action
	 */
	protected function hasAction(string $action): bool
	{
		$method = $this->method($action);

		if (method_exists($this, $method) === false) {
			return false;
		}

		$reflection = new ReflectionMethod($this, $method);

		// `method_exists()` ignores the case of the method name and
		// `::method()` uppercases the first letter of the action, while the
		// permission rules of a role or blueprint are matched
		// case-sensitively. Comparing the action against the declared method
		// name keeps both in sync, so that a case variant of an action can
		// never resolve to a real action method while missing its rule.
		if ($this->action($reflection->getName()) !== $action) {
			return false;
		}

		// action methods are protected, as they are only ever called
		// through `::ensure()`.
		return $reflection->isPrivate() === false;
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
