<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;

return [
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
];
