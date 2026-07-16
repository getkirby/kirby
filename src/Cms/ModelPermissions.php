<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;

/**
 * ModelPermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TModel of \Kirby\Cms\ModelWithContent|\Kirby\Cms\Language
 */
abstract class ModelPermissions
{
	protected const string CATEGORY = 'model';
	protected array $options;

	public static array $cache = [];

	/**
	 * @var TModel
	 */
	protected ModelWithContent|Language $model;

	/**
	 * @param TModel $model
	 */
	public function __construct(ModelWithContent|Language $model)
	{
		$this->model   = $model;
		$this->options = match (true) {
			$model instanceof ModelWithContent => $model->blueprint()->options(),
			default                            => []
		};
	}

	public function __call(string $method, array $arguments = []): bool
	{
		return $this->can($method);
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Can be overridden by specific child classes
	 * to return a model-specific value used to
	 * cache a once determined permission in memory
	 *
	 * @codeCoverageIgnore
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return '';
	}

	/**
	 * Returns whether the current user is allowed to do
	 * a certain action on the model
	 *
	 * @param bool $default Will be returned if $action does not exist
	 */
	public function can(
		string $action,
		bool $default = false
	): bool {
		$user = static::user();
		$role = $user->role();

		// users with the `nobody` role can't execute anything
		if ($role->isNobody() === true) {
			return false;
		}

		// check if the model has the ability to execute this action
		// without breaking system logic. This always takes priority over
		// any other role-based permission rules
		if ($this->model->abilities()->$action() === false) {
			return false;
		}

		// the almighty `kirby` user can execute anything
		if ($user->isKirby() === true) {
			return true;
		}

		return $this->ruleForUser($user, $action) ?? $this->ruleForRole($role, $action) ?? $default;
	}

	/**
	 * Quickly determines a permission for the current user role
	 * and model blueprint unless dynamic checking is required
	 */
	public static function canFromCache(
		ModelWithContent|Language $model,
		string $action,
		bool $default = false
	): bool {
		$role     = $model->kirby()->role()?->id() ?? '__none__';
		$category = static::category($model);
		$cacheKey = $category . '.' . $action . '/' . static::cacheKey($model) . '/' . $role . '/' . ($default === true ? 'true' : 'false');

		if (isset(static::$cache[$cacheKey]) === true) {
			return static::$cache[$cacheKey];
		}

		if (method_exists($model->abilities(), $action) === true) {
			throw new LogicException('Cannot use permission cache for dynamically-determined permission');
		}

		return static::$cache[$cacheKey] = $model->permissions()->can($action, $default);
	}

	/**
	 * Returns whether the current user is not allowed to do
	 * a certain action on the model
	 *
	 * @param bool $default Will be returned if $action does not exist
	 */
	public function cannot(
		string $action,
		bool $default = true
	): bool {
		return $this->can($action, !$default) === false;
	}

	/**
	 * Can be overridden by specific child classes
	 * if the permission category needs to be dynamic
	 */
	public static function category(ModelWithContent|Language $model): string
	{
		return static::CATEGORY;
	}

	/**
	 * Tries to find the permission rule by role and action.
	 * Returns null if no specific rule is set in the role blueprint.
	 */
	public function ruleForRole(Role $role, string $action): bool|null
	{
		return $role->permissions()->for(
			category: static::category($this->model),
			action: $action,
			default: null
		);
	}

	/**
	 * Tries to find the permission rule by user, model and action.
	 * Returns null if no specific rule is set in the model blueprint.
	 */
	public function ruleForUser(User $user, string $action): bool|null
	{
		return match (true) {
			$this->model instanceof ModelWithContent => $this->model->blueprint()->optionForUser($user, $action),
			$this->model instanceof Language         => null
		};
	}

	public function toArray(): array
	{
		$array = [];

		foreach ($this->options as $key => $value) {
			$array[$key] = $this->can($key);
		}

		return $array;
	}

	/**
	 * Returns the currently logged in user
	 */
	protected static function user(): User
	{
		return App::instance()->user() ?? User::nobody();
	}

}
