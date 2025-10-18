<?php

namespace Kirby\Permissions\Sandbox;

use Kirby\Permissions\PagePermissions;

class Page
{
	public function __construct(
		protected string $id,
	) {
	}

	public function isErrorPage(): bool
	{
		return $this->id === 'error';
	}

	public function isHomePage(): bool
	{
		return $this->id === 'home';
	}

	public function permissions(User|null $user = null): PagePermissions
	{
		// get the default page permissions for the user
		$permissions = User::ensure($user)->role()->permissions()->page();

		// the unauthenticated user must not get any additional positive permissions later by accident
		if ($user->role()->isNobody() === true) {
			return $permissions::from(false);
		}

		// the kirby superuser will always get full access
		if ($user->role()->isKirby() === true) {
			return $permissions::from(true);
		}

		// apply page-specific rules and permissions
		return match (true) {
			$this->isErrorPage() => $permissions->merge(PagePermissions::forErrorPage()),
			$this->isHomePage()  => $permissions->merge(PagePermissions::forHomePage()),

			// load options from page blueprint here
			default              => $permissions->merge(new PagePermissions())
		};
	}
}
