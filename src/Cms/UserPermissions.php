<?php

namespace Kirby\Cms;

/**
 * UserPermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserPermissions extends ModelPermissions
{
	protected string $category = 'users';

	public function __construct(User $model)
	{
		parent::__construct($model);

		// change the scope of the permissions,
		// when the current user is this user
		$this->category = $this->user?->is($model) ? 'user' : 'users';
	}

	protected function canChangeRole(): bool
	{
		return $this->model->roles()->count() > 1;
	}

	protected function canCreate(): bool
	{
		// the admin can always create new users
		if ($this->user->isAdmin() === true) {
			return true;
		}

		// users who are not admins cannot create admins
		if ($this->model->isAdmin() === true) {
			return false;
		}

		return true;
	}

	protected function canDelete(): bool
	{
		return $this->model->isLastAdmin() !== true;
	}
}
