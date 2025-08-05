<?php

use Kirby\Cms\Find;
use Kirby\Panel\Controller\View\UsersViewController;

return [
	'users' => [
		'pattern' => 'users',
		'action'  => UsersViewController::class
	],
	'user' => [
		'pattern' => 'users/(:any)',
		'action'  => function (string $id) {
			return Find::user($id)->panel()->view();
		}
	],
	'user.file' => [
		'pattern' => 'users/(:any)/files/(:any)',
		'action'  => function (string $id, string $filename) {
			return Find::file('users/' . $id, $filename)->panel()->view();
		}
	],
];
