<?php

use Kirby\Panel\Controller\Dialog\FieldDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeNameDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeSortDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeTemplateDialogController;
use Kirby\Panel\Controller\Dialog\FileDeleteDialogController;
use Kirby\Panel\Controller\Dialog\SectionDialogController;
use Kirby\Panel\Controller\Dialog\UserChangeEmailDialogController;
use Kirby\Panel\Controller\Dialog\UserChangeLanguageDialogController;
use Kirby\Panel\Controller\Dialog\UserChangeNameDialogController;
use Kirby\Panel\Controller\Dialog\UserChangePasswordDialogController;
use Kirby\Panel\Controller\Dialog\UserChangeRoleDialogController;
use Kirby\Panel\Controller\Dialog\UserCreateDialogController;
use Kirby\Panel\Controller\Dialog\UserDeleteDialogController;
use Kirby\Panel\Controller\Dialog\UserTotpDisableDialogController;
use Kirby\Panel\Controller\Dialog\UserWebauthnDialogController;

return [
	'user.create' => [
		'pattern' => 'users/create',
		'action' => UserCreateDialogController::class
	],
	'user.changeEmail' => [
		'pattern' => 'users/(:any)/changeEmail',
		'action'  => UserChangeEmailDialogController::class
	],
	'user.changeLanguage' => [
		'pattern' => 'users/(:any)/changeLanguage',
		'action'  => UserChangeLanguageDialogController::class
	],
	'user.changeName' => [
		'pattern' => 'users/(:any)/changeName',
		'action'  => UserChangeNameDialogController::class
	],
	'user.changePassword' => [
		'pattern' => 'users/(:any)/changePassword',
		'action'  => UserChangePasswordDialogController::class
	],
	'user.changeRole' => [
		'pattern' => 'users/(:any)/changeRole',
		'action'  => UserChangeRoleDialogController::class
	],
	'user.delete' => [
		'pattern' => 'users/(:any)/delete',
		'action'  => UserDeleteDialogController::class
	],
	'user.totp.disable' => [
		'pattern' => 'users/(:any)/totp/disable',
		'action'  => UserTotpDisableDialogController::class
	],
	'user.webauthn' => [
		'pattern' => 'users/(:any)/webauthn',
		'action'  => UserWebauthnDialogController::class
	],

	'user.fields' => [
		'pattern' => '(users/[^/]+)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],
	'user.sections' => [
		'pattern' => '(users/[^/]+)/sections/(:any)/(:all?)',
		'action'  => SectionDialogController::class
	],

	'user.file.changeName' => [
		'pattern' => '(users/[^/]+)/files/(:any)/changeName',
		'action' => FileChangeNameDialogController::class
	],
	'user.file.changeSort' => [
		'pattern' => '(users/[^/]+)/files/(:any)/changeSort',
		'action' => FileChangeSortDialogController::class
	],
	'user.file.changeTemplate' => [
		'pattern' => '(users/[^/]+)/files/(:any)/changeTemplate',
		'action' => FileChangeTemplateDialogController::class
	],
	'user.file.delete' => [
		'pattern' => '(users/[^/]+)/files/(:any)/delete',
		'action' => FileDeleteDialogController::class
	],

	'user.file.fields' => [
		'pattern' => '(users/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],
	'user.file.sections' => [
		'pattern' => '(users/[^/]+)/files/(:any)/sections/(:any)/(:all?)',
		'action'  => SectionDialogController::class
	],
];
