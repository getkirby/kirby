<?php

namespace Kirby\Cms;

/**
 * UserPermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\ModelPermissions<\Kirby\Cms\User>
 */
class UserPermissions extends ModelPermissions
{
	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param \Kirby\Cms\User $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->role()->id();
	}

	/**
	 * @param \Kirby\Cms\User $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public static function category(ModelWithContent|Language $model): string
	{
		// change the scope of the permissions,
		// when the current user is this user
		return static::user()->is($model) ? 'user' : 'users';
	}
}
