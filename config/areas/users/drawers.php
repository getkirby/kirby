<?php

$fields = require __DIR__ . '/../fields/drawers.php';

return [
	// user field drawers
	'user.fields' => [
		'pattern' => '(users/.*?)/fields/(:any)/(:all?)',
		'load'    => $fields['model']['load'],
		'submit'  => $fields['model']['submit']
	],
	// user file fields drawers
	'user.file.fields' => [
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $fields['file']['load'],
		'submit'  => $fields['file']['submit']
	],
];
