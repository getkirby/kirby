<?php

namespace Kirby\Cms;

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
	protected string $category;
	protected ModelWithContent $model;
	protected array $options;
	protected Permissions $permissions;
	protected User $user;

	public function __construct(ModelWithContent $model)
	{
		$this->model       = $model;
		$this->options     = $model->blueprint()->options();
		$this->user        = $model->kirby()->user() ?? User::nobody();
		$this->permissions = $this->user->role()->permissions();
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

	public function can(string $action): bool
	{
		$user = $this->user->id();
		$role = $this->user->role()->id();

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
		if ($user === 'kirby' && $role === 'admin') {
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

		return $this->permissions->for($this->category, $action);
	}

	public function cannot(string $action): bool
	{
		return $this->can($action) === false;
	}

	public function toArray(): array
	{
		$array = [];

		foreach ($this->options as $key => $value) {
			$array[$key] = $this->can($key);
		}

		return $array;
	}
}
