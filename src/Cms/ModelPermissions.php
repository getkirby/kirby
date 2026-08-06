<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;

/**
 * Boolean facade for the permission and ability guards
 * of a model. All rules live in `\Kirby\Guards\ModelPermissions`
 * and `\Kirby\Guards\ModelAbilities`, this class only turns
 * their exceptions into booleans for the Panel and the API.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TModel of ModelWithContent|Language
 * @deprecated 6.0.0 Use `$model->guards()` instead
 */
abstract class ModelPermissions
{
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
	 * @deprecated 6.0.0 Use `$model->guards()->isAvailable()` instead
	 */
	public function can(
		string $action,
		bool $default = false
	): bool {
		return $this->model->guards()->isAvailable($action, $default);
	}

	/**
	 * Quickly determines a permission for the current user role
	 * and model blueprint unless dynamic checking is required
	 *
	 * @deprecated 6.0.0
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

		if ($model->guards()->abilities()->has($action) === true) {
			throw new LogicException('Cannot use permission cache for dynamically-determined permission');
		}

		return static::$cache[$cacheKey] = $model->permissions()->can($action, $default);
	}

	/**
	 * Returns whether the current user is not allowed to do
	 * a certain action on the model
	 *
	 * @param bool $default Will be returned if $action does not exist
	 * @deprecated 6.0.0 Use `$model->guards()->isAvailable()` instead
	 */
	public function cannot(
		string $action,
		bool $default = true
	): bool {
		return $this->can($action, !$default) === false;
	}

	/**
	 * Returns the permission category of the model
	 *
	 * @deprecated 6.0.0 Use `$model->guards()->permissions()->category()` instead
	 */
	public static function category(ModelWithContent|Language $model): string
	{
		return $model->guards()->permissions()->category();
	}

	public function toArray(): array
	{
		$array = [];

		foreach (array_keys($this->options) as $key) {
			$array[$key] = $this->can($key);
		}

		return $array;
	}
}
