<?php

use Kirby\Cms\Find;
use Kirby\Panel\Controller\Dropdown\LanguagesDropdownController;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'options' => function (string $path) {
			return Find::page($path)->panel()->dropdown();
		}
	],
	'page.languages' => [
		'pattern' => '(pages/.*?)/languages',
		'options' => LanguagesDropdownController::class
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'options' => $files['file']
	],
	'page.file.languages' => [
		'pattern' => '(pages/.*?)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	],
	'site.languages' => [
		'pattern' => '(site)/languages',
		'options' => LanguagesDropdownController::class

	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'options' => $files['file']
	],
	'site.file.languages' => [
		'pattern' => '(site)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	]
];
