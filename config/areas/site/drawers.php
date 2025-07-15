<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	// page field drawers
	'page.fields' => [
		...$fields['model'],
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
	],

	// page file field drawers
	'page.file.fields' => [
		...$fields['file'],
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
	],

	// site field drawers
	'site.fields' => [
		...$fields['model'],
		'pattern' => '(site)/fields/(:any)/(:all?)',
	],

	// site file field drawers
	'site.file.fields' => [
		...$fields['file'],
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
	],
];
