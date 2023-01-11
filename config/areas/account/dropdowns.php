<?php

$dropdowns = require __DIR__ . '/../users/dropdowns.php';

return [
	'account' => [
		'pattern' => '(account)',
		'options' => $dropdowns['user']['options']
	],
	'account.file' => [
		'pattern' => '(account)/files/(:any)',
		'options' => $dropdowns['user.file']['options']
	],
];
