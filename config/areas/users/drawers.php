<?php

use Kirby\Panel\Ui\Drawers\FieldDrawer;

return [
	'user.fields' => [
		'pattern' => '(users/.*?)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forModel(...)
	],
	'user.file.fields' => [
		'pattern' => '(users/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forFile(...)
	]
];
