<?php

use Kirby\Cms\Find;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'user' => [
		'pattern' => 'users/(:any)',
		'options' => function (string $id) {
			return Find::user($id)->panel()->dropdown();
		}
	],
	'user.file' => [
		'pattern' => '(users/.*?)/files/(:any)',
		'options' => $files['file']
	]
];
