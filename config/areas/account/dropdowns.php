<?php

$dropdowns = require __DIR__ . '/../users/dropdowns.php';

return [
	'account' => [
		'pattern' => '(account)',
		'options' => $dropdowns['user']['options']
	],
	'account.languages' => [
		'pattern' => '(account)/languages',
		'options' => $dropdowns['user.languages']['options']
	],
	'account.file' => [
		'pattern' => '(account)/files/(:any)',
		'options' => $dropdowns['user.file']['options']
	],
	'account.file.languages' => [
		'pattern' => '(account)/files/(:any)/languages',
		'options' => $files['language']
	]
];
