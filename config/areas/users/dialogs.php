<?php

use Kirby\Panel\Ui\Dialogs\FieldDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeEmailDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeLanguageDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeNameDialog;
use Kirby\Panel\Ui\Dialogs\UserChangePasswordDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeRoleDialog;
use Kirby\Panel\Ui\Dialogs\UserCreateDialog;
use Kirby\Panel\Ui\Dialogs\UserDeleteDialog;
use Kirby\Panel\Ui\Dialogs\UserTotpDisableDialog;

$files = require __DIR__ . '/../files/dialogs.php';

return [
	'user.create' => [
		'pattern'    => 'users/create',
		'controller' => UserCreateDialog::class
	],
	'user.changeEmail' => [
		'pattern'    => 'users/(:any)/changeEmail',
		'controller' => UserChangeEmailDialog::class
	],
	'user.changeLanguage' => [
		'pattern'    => 'users/(:any)/changeLanguage',
		'controller' => UserChangeLanguageDialog::class
	],
	'user.changeName' => [
		'pattern'    => 'users/(:any)/changeName',
		'controller' => UserChangeNameDialog::class
	],
	'user.changePassword' => [
		'pattern'    => 'users/(:any)/changePassword',
		'controller' => UserChangePasswordDialog::class
	],
	'user.changeRole' => [
		'pattern'    => 'users/(:any)/changeRole',
		'controller' => UserChangeRoleDialog::class
	],
	'user.delete' => [
		'pattern'    => 'users/(:any)/delete',
		'controller' => UserDeleteDialog::class
	],
	'user.fields' => [
		'pattern'    => '(users/.*?)/fields/(:any)/(:all?)',
		'controller' => FieldDialog::forModel(...)
	],
	'user.file.changeName' => [
		...$files['changeName'],
		'pattern' => '(users/.*?)/files/(:any)/changeName',
	],
	'user.file.changeSort' => [
		...$files['changeSort'],
		'pattern' => '(users/.*?)/files/(:any)/changeSort',
	],
	'user.file.changeTemplate' => [
		...$files['changeTemplate'],
		'pattern' => '(users/.*?)/files/(:any)/changeTemplate',
	],
	'user.file.delete' => [
		...$files['delete'],
		'pattern' => '(users/.*?)/files/(:any)/delete',
	],
	'user.file.fields' => [
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDialog::forFile(...)
	],
	'user.totp.disable' => [
		'pattern'    => 'users/(:any)/totp/disable',
		'controller' => UserTotpDisableDialog::class
	],
];
