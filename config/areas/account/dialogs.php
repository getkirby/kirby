<?php

use Kirby\Panel\Ui\Dialogs\UserTotpEnableDialog;

$dialogs = require __DIR__ . '/../users/dialogs.php';

return [
	'account.changeEmail' => [
		'pattern' => '(account)/changeEmail',
		...$dialogs['user.changeEmail']
	],
	'account.changeLanguage' => [
		'pattern' => '(account)/changeLanguage',
		...$dialogs['user.changeLanguage']
	],
	'account.changeName' => [
		'pattern' => '(account)/changeName',
		...$dialogs['user.changeName']
	],
	'account.changePassword' => [
		'pattern' => '(account)/changePassword',
		...$dialogs['user.changePassword']
	],
	'account.changeRole' => [
		'pattern' => '(account)/changeRole',
		...$dialogs['user.changeRole']
	],
	'account.delete' => [
		'pattern' => '(account)/delete',
		...$dialogs['user.delete']
	],
	'account.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		...$dialogs['user.fields']
	],
	'account.file.changeName' => [
		'pattern' => '(account)/files/(:any)/changeName',
		...$dialogs['user.file.changeName']
	],
	'account.file.changeSort' => [
		'pattern' => '(account)/files/(:any)/changeSort',
		...$dialogs['user.file.changeSort']
	],
	'account.file.changeTemplate' => [
		'pattern' => '(account)/files/(:any)/changeTemplate',
		...$dialogs['user.file.changeTemplate']
	],
	'account.file.delete' => [
		'pattern' => '(account)/files/(:any)/delete',
		...$dialogs['user.file.delete']
	],
	'account.file.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		...$dialogs['user.file.fields']
	],
	'account.totp.enable' => [
		'pattern' => '(account)/totp/enable',
		'handler' => fn () => new UserTotpEnableDialog()
	],
	'account.totp.disable' => [
		'pattern' => '(account)/totp/disable',
		...$dialogs['user.totp.disable']
	],
];
