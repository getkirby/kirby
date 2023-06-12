<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'load'    => fn (string $path) => Find::page($path)->panel()->view(),
		'submit'  => function (string $path) {
			return [
			];
		}
	],
	'page.file' => [
		'pattern' => 'pages/(:any)/files/(:any)',
		'action'  => function (string $id, string $filename) {
			return Find::file('pages/' . $id, $filename)->panel()->view();
		}
	],
	'site' => [
		'pattern' => 'site',
		'action'  => fn () => App::instance()->site()->panel()->view()
	],
	'site.file' => [
		'pattern' => 'site/files/(:any)',
		'action'  => function (string $filename) {
			return Find::file('site', $filename)->panel()->view();
		}
	],
];
