<?php

use Kirby\Cms\Find;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'user' => [
		'pattern' => 'users/(:any)',
		'options' => fn (string $id) =>
			Find::user($id)->panel()->dropdown()
	],
	'user.file' => [
		'pattern' => '(users/.*?)/files/(:any)',
		'options' => $files['file']
	]
];
