<?php

namespace Kirby\Panel;

use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
use Throwable;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class Access
{
	/**
	 * Check if the given user has access to the Panel
	 * or to a given area
	 *
	 * @throws \Kirby\Exception\PermissionException when has no access
	 */
	public static function has(
		User|null $user = null,
		string|null $area = null,
		bool $throws = false
	): bool {
		try {
			// a user has to be logged in
			if ($user === null) {
				throw new PermissionException(
					key: 'access.panel'
				);
			}

			// get all access permissions for the user role
			$permissions = $user->role()->permissions()->toArray()['access'];

			// check for general panel access
			if (($permissions['panel'] ?? true) !== true) {
				throw new PermissionException(
					key: 'access.panel'
				);
			}

			// don't check if the area is not defined
			if (empty($area) === true) {
				return true;
			}

			// undefined area permissions means access
			if (isset($permissions[$area]) === false) {
				return true;
			}

			// no access
			if ($permissions[$area] !== true) {
				throw new PermissionException(
					key: 'access.view'
				);
			}

			return true;
		} catch (Throwable $e) {
			if ($throws === true) {
				throw $e;
			}

			return false;
		}
	}
}
