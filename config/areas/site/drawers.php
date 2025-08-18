<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use Kirby\Panel\Controller\Drawer\SectionDrawerController;

return [
	'page.fields' => [
		'pattern' => '(pages/[^/]+)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'page.sections' => [
		'pattern' => '(pages/[^/]+)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
	'page.file.fields' => [
		'pattern' => '(pages/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'page.file.sections' => [
		'pattern' => '(pages/[^/]+)/files/(:any)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'site.sections' => [
		'pattern' => '(site)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDrawerController::class
	],
	'site.file.sections' => [
		'pattern' => '(site)/files/(:any)/sections/(:any)/(:all?)',
		'action'  => SectionDrawerController::class
	],
];
