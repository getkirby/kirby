<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;

/**
 * Role and blueprint based permissions for a `$user` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserPermissions extends ModelPermissions
{
	/**
	 * @var User
	 */
	protected Model $model;

	/**
	 * Users manage their own account under a different
	 * permission category than the accounts of others
	 */
	public function category(): string
	{
		return $this->user->is($this->model) === true ? 'user' : 'users';
	}

	public function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'user.' . $key . '.permission',
			data: [
				'name' => $this->model->username(),
				...$data
			]
		);
	}

	/**
	 * Promoting someone to admin is covered
	 * by the `changeRole` rule
	 */
	protected function ensureToChangeRoleToAdmin(): void
	{
		$this->ensureSetting('changeRole');
	}

	protected function ensureToCreateAvatar(): void
	{
		$this->ensureSetting('update');
		$this->ensureSetting('createAvatar');
	}

	protected function ensureToCreateFirstUser(): void
	{
		// there's no role yet that could grant this permission
	}

	protected function ensureToDeleteAvatar(): void
	{
		$this->ensureSetting('update');
		$this->ensureSetting('deleteAvatar');
	}

	protected function ensureToReplaceAvatar(): void
	{
		$this->ensureSetting('update');
		$this->ensureSetting('replaceAvatar');
	}
}
