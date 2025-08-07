<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;

return [
	'user.fields' => [
		'pattern' => '(users/[^/]+)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'user.file.fields' => [
		'pattern' => '(users/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
];
