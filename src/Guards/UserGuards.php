<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

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
		parent::__construct(
			abilities: new UserAbilities(
				model: $model,
				user: $user
			),
			model: $model,
			permissions: new UserPermissions(
				model: $model,
				user: $user
			),
			user: $user,
			validators: new UserValidators(
				model: $model,
				user: $user
			),
		);
	}

	/**
	 * Checks if the role can be changed
	 *
	 * @throws LogicException If the user is the last admin
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
	 * @throws DuplicateException If the user or the email address already exists
	 * @throws InvalidArgumentException If the id, email, language, password or role is invalid
	 * @throws PermissionException If the user is not allowed to create a new user
	 */
	protected function ensureToCreate(array $props = []): void
	{
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
