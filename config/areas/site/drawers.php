<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	'page.fields' => [
		...$fields['model'],
		'pattern' => '(pages/[^/]+)/fields/(:any)/(:all?)',
	],
	'page.file.fields' => [
		...$fields['file'],
		'pattern' => '(pages/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
	],
	'site.fields' => [
		...$fields['model'],
		'pattern' => '(site)/fields/(:any)/(:all?)',
	],
	'site.file.fields' => [
		...$fields['file'],
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
	],
];
