<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	'user.fields' => [
		...$fields['model'],
		'pattern' => '(users/[^/]+)/fields/(:any)/(:all?)',
	],
	'user.file.fields' => [
		...$fields['file'],
		'pattern' => '(users/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
	],
];
