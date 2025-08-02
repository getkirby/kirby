<?php

use Kirby\Panel\Controller\Dropdown\FileSettingsDropdownController;
use Kirby\Panel\Controller\Dropdown\LanguagesDropdownController;
use Kirby\Panel\Controller\Dropdown\PageSettingsDropdownController;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'options' => PageSettingsDropdownController::class
	],
	'page.languages' => [
		'pattern' => '(pages/[^/]+)/languages',
		'options' => LanguagesDropdownController::class
	],
	'page.file' => [
		'pattern' => '(pages/[^/]+)/files/(:any)',
		'options' => FileSettingsDropdownController::class
	],
	'page.file.languages' => [
		'pattern' => '(pages/[^/]+)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	],
	'site.languages' => [
		'pattern' => '(site)/languages',
		'options' => LanguagesDropdownController::class
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'options' => FileSettingsDropdownController::class
	],
	'site.file.languages' => [
		'pattern' => '(site)/files/(:any)/languages',
		'options' => LanguagesDropdownController::class
	]
];
