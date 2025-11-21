<?php

namespace Kirby\Panel;

use Kirby\Exception\PermissionException;
use Throwable;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Access
{
	public function __construct(
		protected Panel $panel
	) {
	}

	/**
	 * Check if the given user has access to a given area.
	 * Use `*` to check for general Panel access.
	 *
	 * @throws \Kirby\Exception\PermissionException when has no access
	 */
	public function area(
		string|null $area = null,
		bool $throws = false
	): bool {
		try {
			$user = $this->panel->kirby()->user();

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
			if ($area === true || $area === '*' || $area === null) {
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
