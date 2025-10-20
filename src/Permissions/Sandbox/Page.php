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
		$role        = User::ensure($user)->role();
		$permissions = $role->permissions()->page();

		// the permissions for the generic power or weak users cannot be adjusted
		if ($role->isNobody() === true || $role->isKirby() === true) {
			return $permissions;
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
