<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;

/**
 * Abilities for a `$user` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserAbilities extends ModelAbilities
{
	/**
	 * @var User
	 */
	protected Model $model;

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

	protected function ensureToChangeRole(): void
	{
		// prevent demoting the last admin
		if ($this->model->isLastAdmin() === true) {
			$this->error(key: 'changeRole.lastAdmin');
		}

		// protect admin from role changes by non-admin
		if (
			$this->model->isAdmin() === true &&
			$this->user->isAdmin() !== true
		) {
			$this->error(key: 'changeRole.demoteAdmin');
		}
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
}
