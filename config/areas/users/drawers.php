<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	'user.fields' => [
		'pattern' => '(users/.*?)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'user.file.fields' => [
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	]
];
