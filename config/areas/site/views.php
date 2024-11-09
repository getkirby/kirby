<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\I18n;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'action'  => fn (string $path) => Find::page($path)->panel()->view()
	],
	'page.file' => [
		'pattern' => 'pages/(:any)/files/(:any)',
		'action'  => function (string $id, string $filename) {
			return Find::file('pages/' . $id, $filename)->panel()->view();
		}
	],
	'page.preview' => [
		'pattern' => 'pages/(:any)/preview/(changes|latest|compare)',
		'action'  => function (string $path, string $mode) {
			$page = Find::page($path);
			$view = $page->panel()->view();

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back' => $view['props']['link'],
					'mode' => $mode,
					'src'  => [
						'changes' => $page->previewUrl('changes'),
						'latest'  => $page->previewUrl('latest'),
					]
				],
				'title' => $view['props']['title'] . ' | ' . I18n::translate('changes'),
			];
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
	'site.preview' => [
		'pattern' => 'site/preview/(changes|latest|compare)',
		'action'  => function (string $mode) {
			$site = App::instance()->site();
			$view = $site->panel()->view();

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back' => $view['props']['link'],
					'mode' => $mode,
					'src'  => [
						'changes' => $site->previewUrl('changes'),
						'latest'  => $site->previewUrl('latest'),
					]
				],
				'title' => I18n::translate('view.site') . ' | ' . I18n::translate('changes'),
			];
		}
	],
];
