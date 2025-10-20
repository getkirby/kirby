<?php

namespace Kirby\Permissions\Roles;

use Kirby\Permissions\KirbyPermissions;

class ClientRolePermissions extends KirbyPermissions
{
	public static function make(): static
	{
		$permissions = static::from(true);

		$permissions->file->delete = false;
		$permissions->language->delete = false;
		$permissions->languageVariable->delete = false;
		$permissions->page->delete = false;
		$permissions->user->delete = false;

		return $permissions;
	}
}
