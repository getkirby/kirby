<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;

/**
 * ModelPermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelPermissions
{
	protected const CATEGORY = 'model';
	protected array $options;

	protected static array $cache = [];

	public function __construct(protected ModelWithContent|Language $model)
	{
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
	 * @codeCoverageIgnore
	 */
	protected static function cacheKey(ModelWithContent|Language $model): string
	{
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
		if (isset($this->options[$action]) === true) {
			$options = $this->options[$action];

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
		return $permissions->for(static::category($this->model), $action, $default);
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
		$cacheKey = $category . '.' . $action . '/' . static::cacheKey($model) . '/' . $role;

		if (isset(static::$cache[$cacheKey]) === true) {
			return static::$cache[$cacheKey];
		}

		if (method_exists(static::class, 'can' . $action) === true) {
			throw new LogicException('Cannot use permission cache for dynamically-determined permission');
		}

		return static::$cache[$cacheKey] = $model->permissions()->can($action, $role, $default);
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
