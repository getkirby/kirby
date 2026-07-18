<?php

namespace Kirby\Cms;

use Kirby\Toolkit\A;

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

	protected array|null $options = null;

	/**
	 * @var TModel
	 */
	protected ModelWithContent|Language $model;

	/**
	 * @param TModel $model
	 */
	public function __construct(ModelWithContent|Language $model)
	{
		$this->model = $model;
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
	 */
	public function can(
		string $action,
		bool $default = false
	): bool {
		$user   = static::user();
		$userId = $user->id();
		$role   = $user->role()->id();

		// users with the `nobody` role can do nothing
		// that needs a permission check
		if ($role === 'nobody') {
			return false;
		}

		// check for a custom `can` method
		// which would take priority over any other
		// role-based permission rules
		if (
			method_exists($this, 'can' . $action) === true &&
			$this->{'can' . $action}() === false
		) {
			return false;
		}

		// the almighty `kirby` user can do anything
		if ($userId === 'kirby' && $role === 'admin') {
			return true;
		}

		// evaluate the blueprint options block
		$options = $this->options()[$action] ?? null;

		if ($options !== null) {
			if ($options === false) {
				return false;
			}

			if ($options === true) {
				return true;
			}

			if (
				is_array($options) === true &&
				A::isAssociative($options) === true
			) {
				if (isset($options[$role]) === true) {
					return $options[$role];
				}

				if (isset($options['*']) === true) {
					return $options['*'];
				}
			}
		}

		$permissions = $user->role()->permissions();

		return $permissions->for(
			category: static::category($this->model),
			action:   $action,
			default:  $default
		);
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
	protected static function category(ModelWithContent|Language $model): string
	{
		return static::CATEGORY;
	}

	/**
	 * The normalized options block of the model's blueprint
	 * @since 6.0.0
	 */
	public function options(): array
	{
		return $this->options ??= match (true) {
			$this->model instanceof ModelWithContent => $this->model->blueprint()->options(),
			default                                  => []
		};
	}

	public function toArray(): array
	{
		$array = [];

		foreach ($this->options() as $key => $value) {
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
