<?php

use Kirby\Panel\Controller\View\UserFileViewController;
use Kirby\Panel\Controller\View\UsersViewController;
use Kirby\Panel\Controller\View\UserViewController;

return [
	'users' => [
		'pattern' => 'users',
		'action'  => UsersViewController::class,
	],
	'user' => [
		'pattern' => 'users/(:any)',
		'action'  => UserViewController::class,
	],
	'user.file' => [
		'pattern' => '(users/.*?)/files/(:any)',
		'action'  => UserFileViewController::class
	],
];
