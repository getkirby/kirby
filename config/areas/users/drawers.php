<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use Kirby\Panel\Controller\Drawer\SectionDrawerController;
use Kirby\Panel\Controller\Drawer\UserSecurityCodeMethodDrawerController;
use Kirby\Panel\Controller\Drawer\UserSecurityDrawerController;
use Kirby\Panel\Controller\Drawer\UserTotpDrawerController;

return [
	'user.security' => [
		'pattern' => 'users/(:any)/security',
		'action'  => UserSecurityDrawerController::class
	],
	'user.security.method.code' => [
		'pattern' => 'users/(:any)/security/method/code',
		'action'  => UserSecurityCodeMethodDrawerController::class
	],
	'user.security.challenge.totp' => [
		'pattern' => 'users/(:any)/security/challenge/totp',
		'action'  => UserTotpDrawerController::class
	],
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
