<?php

namespace Kirby\Cms;

/**
 * User Abilities
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserAbilities extends ModelAbilities
{
	public function __construct(
		protected User $user
	) {
	}

	public function changeRole(): bool
	{
		// protect admin from role changes by non-admin
		if (
			$this->user->isAdmin() === true &&
			App::instance()->user()?->isAdmin() !== true
		) {
			return false;
		}

		// prevent demoting the last admin
		if ($this->user->isLastAdmin() === true) {
			return false;
		}

		return true;
	}

	public function changeSecret(): bool
	{
		$currentUser = App::instance()->user();

		if (
			$currentUser->is($this->user) === false &&
			$currentUser->isAdmin() === false
		) {
			return false;
		}

		return true;
	}

	public function create(): bool
	{
		// the admin can always create new users
		if (App::instance()->user()?->isAdmin() === true) {
			return true;
		}

		// users who are not admins cannot create admins
		if ($this->user->isAdmin() === true) {
			return false;
		}

		return true;
	}

	public function delete(): bool
	{
		if ($this->user->isLastAdmin() === true) {
			return false;
		}

		if ($this->user->isLastUser() === true) {
			return false;
		}

		return true;
	}
}
