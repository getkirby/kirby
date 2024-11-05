<?php

namespace Kirby\Cms;

/**
 * LanguagePermissions
 *
 * Since the Language class is not a content model,
 * it handles this itself instead of inheriting from the `ModelPermissions` class
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguagePermissions
{
	protected string      $category = 'languages';
	protected Language    $model;
	protected Permissions $permissions;
	protected User        $user;

	public function __construct(Language $model)
	{
		$this->model       = $model;
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

	/**
	 * Returns whether the current user is allowed
	 * to do a certain action on the model
	 *
	 * @param bool $default Will be returned if $action does not exist
	 */
	public function can(
		string $action,
		bool   $default = false
	): bool {
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

		return $this->permissions->for($this->category, $action, $default);
	}

	protected function canDelete(): bool
	{
		return $this->model->isDeletable() === true;
	}

	/**
	 * Returns whether the current user is not allowed
	 * to do a certain action on the model
	 *
	 * @param bool $default Will be returned if $action does not exist
	 */
	public function cannot(
		string $action,
		bool   $default = true
	): bool {
		return $this->can($action, !$default) === false;
	}

	public function toArray(): array
	{
		return [];
	}
}
