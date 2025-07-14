<?php

$dropdowns = require __DIR__ . '/../users/dropdowns.php';

return [
	'account' => [
		...$dropdowns['user'],
		'pattern' => '(account)',
	],
	'account.languages' => [
		...$dropdowns['user.languages'],
		'pattern' => '(account)/languages',
	],
	'account.file' => [
		...$dropdowns['user.file'],
		'pattern' => '(account)/files/(:any)',
	],
	'account.file.languages' => [
		...$dropdowns['user.file.languages'],
		'pattern' => '(account)/files/(:any)/languages',
	]
];
