<?php

use Kirby\Panel\Ui\Drawers\FieldDrawer;

return [
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forModel(...)
	],
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forFile(...)
	],

	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forModel(...)
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDrawer::forFile(...)
	],
];
