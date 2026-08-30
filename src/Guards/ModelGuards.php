<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;

/**
 * Bundles all guard layers for a model object: the abilities,
 * the permissions and the validators. It also composes those
 * layers for the actions of the model and is therefore the
 * only layer that knows the full recipe for an action.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelGuards
{
	use HasActions;

	public function __construct(
		protected ModelAbilities $abilities,
		protected Model $model,
		protected ModelPermissions $permissions,
		protected User $user,
		protected ModelValidators $validators,
	) {
	}

	public function abilities(): ModelAbilities
	{
		return $this->abilities;
	}

	/**
	 * Checks if a method is available to be executed. In comparison to
	 * `ensureExecutable`, this does not validate any input for the action yet.
	 * It will only check the ability and the permissions to execute this action.
	 * This is typically used to check if UI elements in the Panel should be
	 * availabel or not.
	 *
	 * @param $default Used if the action has no permission rule
	 * @throws AbilityException|PermissionException
	 */
	public function ensureAvailable(string $action, bool $default = false): void
	{
		// the model must be able to execute the action without
		// breaking system logic. This always takes priority over
		// any other role-based permission rules.
		$this->abilities()->ensure($action);
		$this->permissions()->ensure($action, $default);
	}

	/**
	 * Runs every check for the given action, including the
	 * validators for its arguments. Actions with a dedicated
	 * `ensureTo<Action>()` method in the child class are handed
	 * over to it, all others fall back to `::ensureAvailable()`
	 * and the validators.
	 *
	 * @throws AbilityException|PermissionException|Exception
	 */
	public function ensureExecutable(string $action, mixed ...$arguments): void
	{
		if ($this->has($action) === true) {
			$this->{$this->method($action)}(...$arguments);

			return;
		}

		$this->ensureAvailable($action);
		$this->validators()->ensure($action, ...$arguments);
	}

	/**
	 * Non-throwing counterpart of `::ensureAvailable()`
	 *
	 * @param $default Used if the action has no permission rule
	 */
	public function isAvailable(string $action, bool $default = false): bool
	{
		try {
			$this->ensureAvailable($action, $default);

			return true;
		} catch (AbilityException | PermissionException) {
			return false;
		}
	}

	/**
	 * Non-throwing counterpart of `::ensureExecutable()`. As the
	 * validators are part of the action, this catches every exception
	 * the layers can throw, not just the ability and permission
	 * exceptions.
	 */
	public function isExecutable(string $action, mixed ...$arguments): bool
	{
		try {
			$this->ensureExecutable($action, ...$arguments);

			return true;
		} catch (Exception) {
			return false;
		}
	}

	public function permissions(): ModelPermissions
	{
		return $this->permissions;
	}

	public function user(): User
	{
		return $this->user;
	}

	public function validators(): ModelValidators
	{
		return $this->validators;
	}
}
