<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	// page field drawers
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		'load'    => $fields['model']['load'],
		'submit'  => $fields['model']['submit']
	],

	// page file field drawers
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $fields['file']['load'],
		'submit'  => $fields['file']['submit'],
	],

	// site field drawers
	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'load'    => $fields['model']['load'],
		'submit'  => $fields['model']['submit'],
	],

	// site file field drawers
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $fields['file']['load'],
		'submit'  => $fields['file']['submit'],
	],
];
