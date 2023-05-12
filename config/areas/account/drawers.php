<?php

$drawers = require __DIR__ . '/../users/drawers.php';

return [
	// account fields drawers
	'account.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $drawers['user.fields']['load'],
		'submit'  => $drawers['user.fields']['submit']
	],

	// account file fields drawers
	'account.file.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $drawers['user.file.fields']['load'],
		'submit'  => $drawers['user.file.fields']['submit']
	],
];
