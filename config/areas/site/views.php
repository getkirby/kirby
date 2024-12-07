<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Exception\PermissionException;
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

			$changesUrl = $page->previewUrl('changes');
			$latestUrl  = $page->previewUrl('latest');

			if ($latestUrl === null) {
				throw new PermissionException('The preview is not available');
			}

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back' => $view['props']['link'],
					'mode' => $mode,
					'src'  => [
						'changes' => $changesUrl,
						'latest'  => $latestUrl,
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

			$changesUrl = $site->previewUrl('changes');
			$latestUrl  = $site->previewUrl('latest');

			if ($latestUrl === null) {
				throw new PermissionException('The preview is not available');
			}

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back' => $view['props']['link'],
					'mode' => $mode,
					'src'  => [
						'changes' => $changesUrl,
						'latest'  => $latestUrl,
					]
				],
				'title' => I18n::translate('view.site') . ' | ' . I18n::translate('changes'),
			];
		}
	],
];
