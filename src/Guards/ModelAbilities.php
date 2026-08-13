<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;

/**
 * Abilities for a model object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelAbilities
{
	use HasActions;

	public function __construct(
		protected Model $model,
		protected User $user
	) {
	}

	/**
	 * Runs the ability check for the given action.
	 * Actions with a dedicated `ensureTo<Action>()` method are
	 * handed over to it, all others are resolved as true
	 *
	 * @throws AbilityException
	 */
	public function ensure(string $action): void
	{
		if ($this->has($action) === true) {
			$this->{$this->method($action)}();
		}
	}

	/**
	 * @param array $details Additional context for the failure,
	 *                       e.g. the field errors of an incomplete page
	 * @throws AbilityException
	 */
	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		throw new AbilityException(
			key: $key,
			data: $data,
			details: $details
		);
	}

	/**
	 * Non-throwing counterpart of `::ensure()`
	 */
	public function may(string $action): bool
	{
		try {
			$this->ensure($action);

			return true;
		} catch (AbilityException) {
			return false;
		}
	}
}
