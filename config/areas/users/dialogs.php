<?php

use Kirby\Panel\Ui\Dialogs\UserChangeEmailDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeLanguageDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeNameDialog;
use Kirby\Panel\Ui\Dialogs\UserChangePasswordDialog;
use Kirby\Panel\Ui\Dialogs\UserChangeRoleDialog;
use Kirby\Panel\Ui\Dialogs\UserCreateDialog;
use Kirby\Panel\Ui\Dialogs\UserDeleteDialog;
use Kirby\Panel\Ui\Dialogs\UserTotpDisableDialog;

$fields = require __DIR__ . '/../fields/dialogs.php';
$files = require __DIR__ . '/../files/dialogs.php';

return [
	'user.create' => [
		'pattern' => 'users/create',
		'handler' => fn () => new UserCreateDialog()
	],
	'user.changeEmail' => [
		'pattern' => 'users/(:any)/changeEmail',
		'handler' => UserChangeEmailDialog::for(...)
	],
	'user.changeLanguage' => [
		'pattern' => 'users/(:any)/changeLanguage',
		'handler' => UserChangeLanguageDialog::for(...)
	],
	'user.changeName' => [
		'pattern' => 'users/(:any)/changeName',
		'handler' => UserChangeNameDialog::for(...)
	],
	'user.changePassword' => [
		'pattern' => 'users/(:any)/changePassword',
		'handler' => UserChangePasswordDialog::for(...)
	],
	'user.changeRole' => [
		'pattern' => 'users/(:any)/changeRole',
		'handler' => UserChangeRoleDialog::for(...)
	],
	'user.delete' => [
		'pattern' => 'users/(:any)/delete',
		'handler' => UserDeleteDialog::for(...)
	],
	'user.fields' => [
		...$fields['model'],
		'pattern' => '(users/.*?)/fields/(:any)/(:all?)',
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
		...$fields['file'],
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
	],
	'user.totp.disable' => [
		'pattern' => 'users/(:any)/totp/disable',
		'handler' => UserTotpDisableDialog::for(...)
	],
];
