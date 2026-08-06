<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\User;

/**
 * Role and blueprint based permissions for a `$user` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
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

	/**
	 * Secrets don't have their own permission.
	 * They are covered by the `changePassword` rule.
	 */
	protected function ensureToChangeSecret(): void
	{
		$this->ensureSetting('changePassword');
	}

	/**
	 * Avatars don't have their own permission.
	 * They are covered by the `update` rule.
	 */
	protected function ensureToCreateAvatar(): void
	{
		$this->ensureSetting('update');
	}

	protected function ensureToCreateFirstUser(): void
	{
		// there's no role yet that could grant this permission
	}

	/**
	 * Avatars don't have their own permission.
	 * They are covered by the `update` rule.
	 */
	protected function ensureToDeleteAvatar(): void
	{
		$this->ensureSetting('update');
	}

	/**
	 * Avatars don't have their own permission.
	 * They are covered by the `update` rule.
	 */
	protected function ensureToReplaceAvatar(): void
	{
		$this->ensureSetting('update');
	}
}
