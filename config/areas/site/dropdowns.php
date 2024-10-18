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
		'pattern' => '(pages/.*?)/languages',
		'options' => function (string $path) {
			$model = Find::parent($path);
			return (new LanguagesDropdown($model))->options();
		}
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'options' => $files['file']
	],
	'page.file.languages' => [
		'pattern' => '(pages/.*?)/files/(:any)/languages',
		'options' => function (string $parent, string $filename) {
			$file = Find::file($parent, $filename);
			return (new LanguagesDropdown($file))->options();
		}
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
		'pattern' => 'site/files/(:any)/languages',
		'options' => function (string $filename) {
			$file = App::instance()->site()->file($filename);
			return (new LanguagesDropdown($file))->options();
		}
	]
];
