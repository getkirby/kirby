<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;
use WeakMap;

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

	protected static WeakMap|null $cache = null;

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
	 * @param bool $default Used if the action has no permission rule
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
	 * Returns the guards for the model,
	 * bound to the currently authenticated user.
	 */
	public static function for(Model $model): static
	{
		$user   = User::ensure();
		$cache  = static::$cache ??= new WeakMap();
		$guards = $cache[$model] ?? null;

		// the guards are bound to the current user and must
		// be rebuilt whenever the user changes
		if (
			$guards instanceof static === true &&
			$guards->user() === $user
		) {
			return $guards;
		}

		// every concrete guards class narrows the constructor
		// down to the model and the user
		/** @psalm-suppress TooFewArguments */
		return $cache[$model] = new static(
			model: $model,
			user: $user
		);
	}

	/**
	 * Non-throwing counterpart of `::ensureAvailable()`
	 *
	 * @param bool $default Used if the action has no permission rule
	 */
	public function isAvailable(string $action, bool $default = false): bool
	{
		// A permission without its own action method comes down to the
		// plain rule, which is the cheapest check there is. A denial
		// settles the question on its own, whatever the abilities would
		// say, so reading it first keeps every denied action off the
		// throwing path - which the Panel walks for every model in a list.
		$plain = $this->permissions()->has($action) === false;

		if (
			$plain === true &&
			$this->permissions()->setting($action, $default) !== true
		) {
			return false;
		}

		try {
			// with the permission settled, only the ability is left;
			// `::ensure()` is a no-op for an action without a method
			if ($plain === true) {
				$this->abilities()->ensure($action);
			} else {
				$this->ensureAvailable($action, $default);
			}

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

	/**
	 * Returns the availability of every action that the
	 * blueprint of the model defines an option for. This is
	 * what the Panel and the API hand to the frontend.
	 */
	public function toArray(): array
	{
		// only models with a blueprint can define options
		if ($this->model instanceof ModelWithContent === false) {
			return [];
		}

		/** @var array<string, mixed> $options */
		$options = $this->model->blueprint()->options();
		$array   = [];

		foreach (array_keys($options) as $action) {
			$array[$action] = $this->isAvailable($action);
		}

		return $array;
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
