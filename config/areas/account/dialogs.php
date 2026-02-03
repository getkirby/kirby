<?php

use Kirby\Panel\UserTotpEnableDialog;

$dialogs = require __DIR__ . '/../users/dialogs.php';

return [
	'account.changeEmail' => [
		...$dialogs['user.changeEmail'],
		'pattern' => '(account)/changeEmail',
	],
	'account.changeLanguage' => [
		...$dialogs['user.changeLanguage'],
		'pattern' => '(account)/changeLanguage',
	],
	'account.changeName' => [
		...$dialogs['user.changeName'],
		'pattern' => '(account)/changeName',
	],
	'account.changePassword' => [
		...$dialogs['user.changePassword'],
		'pattern' => '(account)/changePassword',
	],
	'account.changeRole' => [
		...$dialogs['user.changeRole'],
		'pattern' => '(account)/changeRole',
	],
	'account.delete' => [
		...$dialogs['user.delete'],
		'pattern' => '(account)/delete',
	],
	'account.fields' => [
		...$dialogs['user.fields'],
		'pattern' => '(account)/fields/(:any)/(:all?)',
	],
	'account.file.changeName' => [
		...$dialogs['user.file.changeName'],
		'pattern' => '(account)/files/(:any)/changeName',
	],
	'account.file.changeSort' => [
		...$dialogs['user.file.changeSort'],
		'pattern' => '(account)/files/(:any)/changeSort',
	],
	'account.file.changeTemplate' => [
		...$dialogs['user.file.changeTemplate'],
		'pattern' => '(account)/files/(:any)/changeTemplate',
	],
	'account.file.delete' => [
		...$dialogs['user.file.delete'],
		'pattern' => '(account)/files/(:any)/delete',
	],
	'account.file.fields' => [
		...$dialogs['user.file.fields'],
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
	],
	'account.totp.enable' => [
		'pattern' => '(account)/totp/enable',
		'load'    => fn () => (new UserTotpEnableDialog())->load(),
		'submit'  => fn () => (new UserTotpEnableDialog())->submit()
	],
	'account.totp.disable' => [
		...$dialogs['user.totp.disable'],
		'pattern' => '(account)/totp/disable',
	],
];
