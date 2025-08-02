<?php

use Kirby\Cms\Find;
use Kirby\Panel\Ui\Buttons\LanguagesDropdown;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'user' => [
		'pattern' => 'users/(:any)',
		'options' => fn (string $id) =>
			Find::user($id)->panel()->dropdown()
	],
	'user.languages' => [
		'pattern' => 'users/(:any)/languages',
		'options' => function (string $id) {
			$user = Find::user($id);
			return (new LanguagesDropdown($user))->options();
		}
	],
	'user.file' => [
		'pattern' => '(users/[^/]+)/files/(:any)',
		'options' => $files['file']
	],
	'user.file.languages' => [
		'pattern' => '(users/[^/]+)/files/(:any)/languages',
		'options' => $files['language']
	]
];
