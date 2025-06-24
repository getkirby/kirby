<?php

use Kirby\Panel\UserTotpEnableDialog;

$dialogs = require __DIR__ . '/../users/dialogs.php';

return [
	// change email
	'account.changeEmail' => [
		'pattern' => '(account)/changeEmail',
		'load'    => $dialogs['user.changeEmail']['load'],
		'submit'  => $dialogs['user.changeEmail']['submit'],
	],

	// change language
	'account.changeLanguage' => [
		'pattern' => '(account)/changeLanguage',
		'load'    => $dialogs['user.changeLanguage']['load'],
		'submit'  => $dialogs['user.changeLanguage']['submit'],
	],

	// change name
	'account.changeName' => [
		'pattern' => '(account)/changeName',
		'load'    => $dialogs['user.changeName']['load'],
		'submit'  => $dialogs['user.changeName']['submit'],
	],

	// change password
	'account.changePassword' => [
		'pattern' => '(account)/changePassword',
		'load'    => $dialogs['user.changePassword']['load'],
		'submit'  => $dialogs['user.changePassword']['submit'],
	],

	// change role
	'account.changeRole' => [
		'pattern' => '(account)/changeRole',
		'load'    => $dialogs['user.changeRole']['load'],
		'submit'  => $dialogs['user.changeRole']['submit'],
	],

	// delete
	'account.delete' => [
		'pattern' => '(account)/delete',
		'load'    => $dialogs['user.delete']['load'],
		'submit'  => $dialogs['user.delete']['submit'],
	],

	// account fields dialogs
	'account.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $dialogs['user.fields']['load'],
		'submit'  => $dialogs['user.fields']['submit']
	],

	// change file name
	'account.file.changeName' => [
		'pattern' => '(account)/files/(:any)/changeName',
		'load'    => $dialogs['user.file.changeName']['load'],
		'submit'  => $dialogs['user.file.changeName']['submit'],
	],

	// change file sort
	'account.file.changeSort' => [
		'pattern' => '(account)/files/(:any)/changeSort',
		'load'    => $dialogs['user.file.changeSort']['load'],
		'submit'  => $dialogs['user.file.changeSort']['submit'],
	],

	// change file template
	'account.file.changeTemplate' => [
		'pattern' => '(account)/files/(:any)/changeTemplate',
		'load'    => $dialogs['user.file.changeTemplate']['load'],
		'submit'  => $dialogs['user.file.changeTemplate']['submit'],
	],

	// delete
	'account.file.delete' => [
		'pattern' => '(account)/files/(:any)/delete',
		'load'    => $dialogs['user.file.delete']['load'],
		'submit'  => $dialogs['user.file.delete']['submit'],
	],

	// account file fields dialogs
	'account.file.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $dialogs['user.file.fields']['load'],
		'submit'  => $dialogs['user.file.fields']['submit']
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
		'load'    => $dialogs['user.totp.disable']['load'],
		'submit'  => $dialogs['user.totp.disable']['submit']
	],
];
