<?php

$fields = require __DIR__ . '/../fields/drawers.php';
$sections = require __DIR__ . '/../sections/drawers.php';

return [
	'user.fields' => [
		'pattern' => '(users/.*?)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'user.sections' => [
		'pattern' => '(users/.*?)/sections/(:any)/(:all?)',
		...$sections['model']
	],
	'user.file.fields' => [
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	],
	'user.file.sections' => [
		'pattern' => '(users/.*?)/files/(:any)/sections/(:any)/(:all?)',
		...$sections['file']
	],
];
