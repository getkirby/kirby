<?php

use Kirby\Cms\Find;
use Kirby\Panel\Ui\Buttons\LanguagesDropdown;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'options' => function (string $path) {
			return Find::page($path)->panel()->dropdown();
		}
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'options' => $files['file']
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'options' => $files['file']
	],
	'languages' => [
		'pattern' => '(site|pages/.*?)/languages',
		'options' => function (string $path) {
			$model = Find::parent($path);
			return (new LanguagesDropdown($model))->options();
		}
	]
];
