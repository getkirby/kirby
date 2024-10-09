<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'action'  => fn (string $path) => Find::page($path)->panel()->view()
	],
	'page.changes.compare' => [
		'pattern' => 'pages/(:any)/changes/compare',
		'action'  => function (string $path) {
			$page = Find::page($path);

			return [
				'component' => 'k-page-comparison-view',
				'props'     => [
					'changes'   => $page->previewUrl() . '?_version=changes',
					'backlink'  => $page->panel()->url(true),
					'lock'      => $page->lock()->toArray(),
					'published' => $page->previewUrl(),
				],
				'title' => $page->title()->value(),
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
