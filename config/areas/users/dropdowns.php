<?php

use Kirby\Cms\Find;
use Kirby\Panel\Controller\Dropdown\LanguagesDropdownController;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'user' => [
		'pattern' => 'users/(:any)',
		'options' => fn (string $id) =>
			Find::user($id)->panel()->dropdown()
	],
	'user.languages' => [
		'pattern' => '(users/.*?)/languages',
		'options' => LanguagesDropdownController::class
	],
	'user.file' => [
		'pattern' => '(users/.*?)/files/(:any)',
		'options' => $files['file']
	],
	'user.file.languages' => [
		'pattern' => '(users/.*?)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	]
];
