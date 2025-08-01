<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use Kirby\Panel\Controller\Drawer\SectionDrawerController;

return [
	'user.fields' => [
		'pattern' => '(users/[^/]+)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'user.sections' => [
		'pattern' => '(users/[^/]+)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
	'user.file.fields' => [
		'pattern' => '(users/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'user.file.sections' => [
		'pattern' => '(users/[^/]+)/files/(:any)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
];
