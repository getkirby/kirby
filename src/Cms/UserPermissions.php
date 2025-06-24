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
	/**
	 * Used to cache once determined permissions in memory
	 */
	protected static function cacheKey(ModelWithContent|Language $model): string
	{
		return $model->role()->id();
	}

	protected function canChangeRole(): bool
	{
		// protect admin from role changes by non-admin
		if (
			$this->model->isAdmin() === true &&
			static::user()->isAdmin() !== true
		) {
			return false;
		}

		// prevent demoting the last admin
		if ($this->model->isLastAdmin() === true) {
			return false;
		}

		return true;
	}

	protected function canCreate(): bool
	{
		// the admin can always create new users
		if (static::user()->isAdmin() === true) {
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

	protected static function category(ModelWithContent|Language $model): string
	{
		// change the scope of the permissions,
		// when the current user is this user
		return static::user()->is($model) ? 'user' : 'users';
	}
}
