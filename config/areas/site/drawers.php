<?php

$fields = require __DIR__ . '/../fields/drawers.php';
$files = require __DIR__ . '/../files/drawers.php';

return [
	'page.fields' => [
		...$fields['model'],
		'pattern' => '(pages/[^/]+)/fields/(:any)/(:all?)',
	],

	// page file
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'load'    => $files['file']['load'],
		'submit'  => $files['file']['submit'],
	],

	// page file field drawers
	'page.file.fields' => [
		...$fields['file'],
		'pattern' => '(pages/[^/]+)/files/(:any)/fields/(:any)/(:all?)',
	],
	'site.fields' => [
		...$fields['model'],
		'pattern' => '(site)/fields/(:any)/(:all?)',
	],

	// site file
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'load'    => $files['file']['load'],
		'submit'  => $files['file']['submit'],
	],

	// site file field drawers
	'site.file.fields' => [
		...$fields['file'],
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
	],
];
