<?php

use Kirby\Panel\Controller\Dropdown\FileSettingsDropdownController;
use Kirby\Panel\Controller\Dropdown\LanguagesDropdownController;
use Kirby\Panel\Controller\Dropdown\UserSettingsDropdownController;

return [
	'user' => [
		'pattern' => 'users/(:any)',
		'options' => UserSettingsDropdownController::class
	],
	'user.languages' => [
		'pattern' => '(users/[^/]+)/languages',
		'options' => LanguagesDropdownController::class
	],
	'user.file' => [
		'pattern' => '(users/[^/]+)/files/(:any)',
		'options' => FileSettingsDropdownController::class
	],
	'user.file.languages' => [
		'pattern' => '(users/[^/]+)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	]
];
