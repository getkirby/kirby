<?php

use Kirby\Permissions\KirbyPermissions;

return function () {
	$permissions = KirbyPermissions::from(true);

	$permissions->file->delete = false;
	$permissions->language->delete = false;
	$permissions->languageVariable->delete = false;
	$permissions->page->delete = false;
	$permissions->user->delete = false;

	return $permissions;
};
