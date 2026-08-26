<?php

use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use Kirby\Panel\Controller\Drawer\SectionDrawerController;
use Kirby\Panel\Controller\Drawer\UserEmailChallengeDrawerController;
use Kirby\Panel\Controller\Drawer\UserSecurityCodeMethodDrawerController;
use Kirby\Panel\Controller\Drawer\UserSecurityDrawerController;
use Kirby\Panel\Controller\Drawer\UserTotpDrawerController;
use Kirby\Panel\Controller\Drawer\UserWebauthnDrawerController;

return [
	'user.security' => [
		'pattern' => 'users/(:any)/security',
		'action'  => UserSecurityDrawerController::class
	],
	'user.security.method.code' => [
		'pattern' => 'users/(:any)/security/method/code',
		'action'  => UserSecurityCodeMethodDrawerController::class
	],
	'user.security.challenge.email' => [
		'pattern' => 'users/(:any)/security/challenge/email',
		'action'  => UserEmailChallengeDrawerController::class
	],
	'user.security.challenge.totp' => [
		'pattern' => 'users/(:any)/security/challenge/totp',
		'action'  => UserTotpDrawerController::class
	],
	'user.security.method.webauthn' => [
		'pattern' => 'users/(:any)/security/method/webauthn',
		'action'  => UserWebauthnDrawerController::class
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
