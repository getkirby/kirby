<?php

use Kirby\Cms\App;
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
	'page.languages' => [
		'pattern' => 'pages/(:any)/languages',
		'options' => function (string $path) {
			$page = Find::page($path);
			return (new LanguagesDropdown($page))->options();
		}
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'options' => $files['file']
	],
	'page.file.languages' => [
		'pattern' => '(pages/.*?)/files/(:any)/languages',
		'options' => $files['language']
	],
	'site.languages' => [
		'pattern' => 'site/languages',
		'options' => function () {
			$site = App::instance()->site();
			return (new LanguagesDropdown($site))->options();
		}
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'options' => $files['file']
	],
	'site.file.languages' => [
		'pattern' => '(site)/files/(:any)/languages',
		'options' => $files['language']
	]
];
