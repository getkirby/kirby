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
	protected $category;
	protected $model;
	protected $options;
	protected $permissions;
	protected $user;

	/**
	 * @param string $method
	 * @param array $arguments
	 * @return bool
	 */
	public function __call(string $method, array $arguments = []): bool
	{
		return $this->can($method);
	}

	/**
	 * ModelPermissions constructor
	 *
	 * @param \Kirby\Cms\Model $model
	 */
	public function __construct(Model $model)
	{
		$this->model       = $model;
		$this->options     = $model->blueprint()->options();
		$this->user        = $model->kirby()->user() ?? User::nobody();
		$this->permissions = $this->user->role()->permissions();
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	public function can(string $action): bool
	{
		$role = $this->user->role()->id();

		if ($role === 'nobody') {
			return false;
		}

		// check for a custom overall can method
		if (
			method_exists($this, 'can' . $action) === true &&
			$this->{'can' . $action}() === false
		) {
			return false;
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
				return $options[$role] ?? $options['*'] ?? false;
			}
		}

		return $this->permissions->for($this->category, $action);
	}

	/**
	 * @param string $action
	 * @return bool
	 */
	public function cannot(string $action): bool
	{
		return $this->can($action) === false;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		$array = [];

		foreach ($this->options as $key => $value) {
			$array[$key] = $this->can($key);
		}

		return $array;
	}
}
