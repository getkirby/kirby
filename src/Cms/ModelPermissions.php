<?php

namespace Kirby\Cms;

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
