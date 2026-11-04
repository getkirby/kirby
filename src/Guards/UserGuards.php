<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use SensitiveParameter;

/**
 * Bundles all guards for a `$user` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @method UserAbilities abilities()
 * @method UserPermissions permissions()
 * @method UserValidators validators()
 */
class UserGuards extends ModelGuards
{
	/**
	 * @var User
	 */
	protected Model $model;

	/**
	 * @var UserAbilities
	 */
	protected ModelAbilities $abilities;

	/**
	 * @var UserPermissions
	 */
	protected ModelPermissions $permissions;

	/**
	 * @var UserValidators
	 */
	protected ModelValidators $validators;

	public function __construct(
		User $model,
		User $user
	) {
		parent::__construct($model, $user);

		$this->abilities   = new UserAbilities($model, $user);
		$this->permissions = new UserPermissions($model, $user);
		$this->validators  = new UserValidators($model, $user);
	}

	/**
	 * Checks if the role can be changed
	 *
	 * @throws AbilityException If the user is the last admin, or if a non-admin changes the role of an admin or promotes a user to admin
	 * @throws InvalidArgumentException If the role is invalid
	 * @throws PermissionException If the user is not allowed to change the role
	 */
	protected function ensureToChangeRole(string $role): void
	{
		$action = match ($role) {
			'admin' => 'changeRoleToAdmin',
			default => 'changeRole'
		};

		$this->ensureAvailable($action);
		$this->validators()->ensure('changeRole', $role);
	}

	/**
	 * Checks if the user can be created
	 *
	 * @throws AbilityException If a non-admin creates an admin
	 * @throws DuplicateException If the user or the email address already exists
	 * @throws InvalidArgumentException If the id, email, language, password or role is invalid
	 * @throws PermissionException If the user is not allowed to create a new user
	 */
	protected function ensureToCreate(
		#[SensitiveParameter]
		array $props = []
	): void {
		// the first user can always be created,
		// but a password is required
		if ($this->model->kirby()->users()->count() === 0) {
			$this->ensureAvailable('createFirstUser');
			$this->validators()->ensure('createFirstUser', $props);

			return;
		}

		$this->ensureAvailable('create');
		$this->validators()->ensure('create', $props);
	}
}
