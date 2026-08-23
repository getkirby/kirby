<?php

namespace Kirby\Cms;

/**
 * UserPermissions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends ModelPermissions<User>
 * @deprecated 6.0.0 Use `$user->guards()` instead
 */
class UserPermissions extends ModelPermissions
{
	/**
	 * Used to cache once determined permissions in memory
	 *
	 * @param User $model
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	protected static function cacheKey(
		ModelWithContent|Language $model
	): string {
		return $model->role()->id();
	}
}
