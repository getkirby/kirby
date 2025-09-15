<?php

$drawers = require __DIR__ . '/../users/drawers.php';

return [
	'account.fields' => [
		...$drawers['user.fields'],
		'pattern' => '(account)/fields/(:any)/(:all?)',
	],
	'account.file.fields' => [
		...$drawers['user.file.fields'],
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
	],
];
