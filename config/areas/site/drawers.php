<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	],

	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	],
];
