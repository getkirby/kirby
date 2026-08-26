<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;

/**
 * Abilities for a `$user` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserAbilities extends ModelAbilities
{
	/**
	 * @var User
	 */
	protected Model $model;

	/**
	 * Only admins are allowed to run actions on other admins
	 * that could lock them out of their account
	 */
	protected function ensureAdmin(string $action): void
	{
		if (
			$this->model->isAdmin() === true &&
			$this->user->isAdmin() !== true
		) {
			$this->error(key: $action . '.admin');
		}
	}

	/**
	 * A new email address would lock the admin out of
	 * their account and could be used to take it over
	 */
	protected function ensureToChangeEmail(): void
	{
		$this->ensureAdmin('changeEmail');
	}

	/**
	 * A new password would lock the admin out of
	 * their account and could be used to take it over
	 */
	protected function ensureToChangePassword(): void
	{
		$this->ensureAdmin('changePassword');
	}

	protected function ensureToChangeRole(): void
	{
		// prevent demoting the last admin
		if ($this->model->isLastAdmin() === true) {
			$this->error(key: 'changeRole.lastAdmin');
		}

		$this->ensureAdmin('changeRole');
	}

	protected function ensureToChangeRoleToAdmin(): void
	{
		if ($this->user->isAdmin() !== true) {
			$this->error(key: 'changeRole.toAdmin');
		}
	}

	protected function ensureToChangeSecret(): void
	{
		// users can change the secrets for their own account
		if ($this->user->is($this->model) === true) {
			return;
		}

		// admins can change the secrets for other users
		if ($this->user->isAdmin() === true) {
			return;
		}

		$this->error(key: 'changeSecret');
	}

	protected function ensureToCreate(): void
	{
		// the admin can always create new users
		if ($this->user->isAdmin() === true) {
			return;
		}

		// users who are not admins cannot create admins
		if ($this->model->isAdmin() === true) {
			$this->error(key: 'create.admin');
		}
	}

	protected function ensureToCreateAvatar(): void
	{
		if ($this->model->avatar() !== null) {
			$this->error(key: 'avatar.duplicate');
		}
	}

	protected function ensureToCreateFirstUser(): void
	{
		if ($this->model->kirby()->users()->count() !== 0) {
			$this->error(key: 'create.first');
		}
	}

	protected function ensureToDelete(): void
	{
		if ($this->model->isLastAdmin() === true) {
			$this->error(key: 'delete.lastAdmin');
		}

		if ($this->model->isLastUser() === true) {
			$this->error(key: 'delete.lastUser');
		}

		$this->ensureAdmin('delete');
	}

	protected function ensureToDeleteAvatar(): void
	{
		if ($this->model->avatar() === null) {
			$this->error(key: 'avatar.notFound');
		}
	}

	protected function ensureToReplaceAvatar(): void
	{
		if ($this->model->avatar() === null) {
			$this->error(key: 'avatar.notFound');
		}
	}

	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		parent::error(
			key: 'user.' . $key,
			data: [
				'name' => $this->model->username(),
				...$data
			],
			details: $details
		);
	}
}
