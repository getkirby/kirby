<?php

use Kirby\Panel\Ui\Dialogs\UserTotpEnableDialog;

$dialogs = require __DIR__ . '/../users/dialogs.php';

return [
	// change email
	'account.changeEmail' => [
		...$dialogs['user.changeEmail'],
		'pattern' => '(account)/changeEmail',
	],

	// change language
	'account.changeLanguage' => [
		...$dialogs['user.changeLanguage'],
		'pattern' => '(account)/changeLanguage',
	],

	// change name
	'account.changeName' => [
		...$dialogs['user.changeName'],
		'pattern' => '(account)/changeName',
	],

	// change password
	'account.changePassword' => [
		...$dialogs['user.changePassword'],
		'pattern' => '(account)/changePassword',
	],

	// change role
	'account.changeRole' => [
		...$dialogs['user.changeRole'],
		'pattern' => '(account)/changeRole',
	],

	// delete
	'account.delete' => [
		...$dialogs['user.delete'],
		'pattern' => '(account)/delete',
	],

	// account fields dialogs
	'account.fields' => [
		...$dialogs['user.fields'],
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
	],

	// change file name
	'account.file.changeName' => [
		...$dialogs['user.file.changeName'],
		'pattern' => '(account)/files/(:any)/changeName',
	],

	// change file sort
	'account.file.changeSort' => [
		...$dialogs['user.file.changeSort'],
		'pattern' => '(account)/files/(:any)/changeSort',
	],

	// change file template
	'account.file.changeTemplate' => [
		...$dialogs['user.file.changeTemplate'],
		'pattern' => '(account)/files/(:any)/changeTemplate',
	],

	// delete
	'account.file.delete' => [
		...$dialogs['user.file.delete'],
		'pattern' => '(account)/files/(:any)/delete',
	],

	// account file fields dialogs
	'account.file.fields' => [
		...$dialogs['user.file.fields'],
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
	],

	// account enable TOTP
	'account.totp.enable' => [
		'pattern' => '(account)/totp/enable',
		'load'    => fn () => (new UserTotpEnableDialog())->load(),
		'submit'  => fn () => (new UserTotpEnableDialog())->submit()
	],

	// account disable TOTP
	'account.totp.disable' => [
		'pattern' => '(account)/totp/disable',
		...$dialogs['user.totp.disable'],
	],
];
