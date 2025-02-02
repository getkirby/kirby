<?php

$drawers = require __DIR__ . '/../users/drawers.php';

return [
	'account.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		...$drawers['user.fields']
	],
	'account.file.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		...$drawers['user.file.fields']
	],
];
