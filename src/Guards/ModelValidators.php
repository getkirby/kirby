<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * Validators for the input of a model action
 *
 * Every single validator is named after the argument it
 * checks. The `ensureTo<Action>()` methods on top of them
 * combine all validators that an action needs.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelValidators
{
	use HasActions;

	public function __construct(
		protected Model $model,
		protected User $user
	) {
	}

	/**
	 * Runs all validators for the given action. Actions with a
	 * dedicated `ensureTo<Action>()` method are handed over to it,
	 * all others have nothing to validate.
	 */
	public function ensure(string $action, mixed ...$arguments): void
	{
		if ($this->has($action) === true) {
			$this->{$this->method($action)}(...$arguments);
		}
	}

	/**
	 * Throws for a failed validation. Can be overridden by child
	 * classes to prefix the key or add model-specific data.
	 *
	 * @throws InvalidArgumentException
	 */
	public function error(string $key, array $data = []): never
	{
		throw new InvalidArgumentException(
			key: $key,
			data: $data
		);
	}

	/**
	 * Non-throwing counterpart of `::ensure()`. The validators
	 * throw more specific exception classes than the other
	 * layers, so this catches every Kirby exception.
	 */
	public function may(string $action, mixed ...$arguments): bool
	{
		try {
			$this->ensure($action, ...$arguments);

			return true;
		} catch (Exception) {
			return false;
		}
	}
}
