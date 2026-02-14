<?php

$drawers = require __DIR__ . '/../users/drawers.php';

return [
	'account.security' => [
		...$drawers['user.security'],
		'pattern' => '(account)/security',
	],
	'account.security.method.code' => [
		...$drawers['user.security.method.code'],
		'pattern' => '(account)/security/method/code',
	],
	'account.fields' => [
		...$drawers['user.fields'],
		'pattern' => '(account)/fields/(:any)/(:all?)',
	],
	'account.sections' => [
		...$drawers['user.sections'],
		'pattern' => '(account)/sections/(:any)/(:all?)',
	],
	'account.file.fields' => [
		...$drawers['user.file.fields'],
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
	],
	'account.file.sections' => [
		...$drawers['user.file.sections'],
		'pattern' => '(account)/files/(:any)/sections/(:any)/(:all?)',
	],
];
