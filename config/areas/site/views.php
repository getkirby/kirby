<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\View\ModelPreviewViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\Toolkit\A;
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
		'action'  => function (string $path, string $versionId) {
			// handle redirect if view was reloaded with a redirect URL
			// after navigating to a different page inside the preview browser
			if ($redirect = ModelPreviewViewController::redirect($versionId)) {
				Panel::go($redirect);
			}

			$page = Find::page($path);
			$view = $page->panel()->view();
			$src  = ModelPreviewViewController::src($page);

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back'    => $view['props']['link'],
					'buttons' => fn () =>
						ViewButtons::view('page.preview', model: $page)
							->defaults(
								'page.versions',
								'languages',
							)
							->bind(['versionId' => $versionId])
							->render(),
					'src'       => $src,
					'versionId' => $versionId,
				],
				'title' => $view['props']['title'] . ' | ' . I18n::translate('preview'),
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
		'action'  => function (string $versionId) {
			// handle redirect if view was reloaded with a redirect URL
			// after navigating to a different page inside the preview browser
			if ($redirect = ModelPreviewViewController::redirect($versionId)) {
				Panel::go($redirect);
			}

			$site = App::instance()->site();
			$view = $site->panel()->view();
			$src  = ModelPreviewViewController::src($site);

			return [
				'component' => 'k-preview-view',
				'props'     => [
					...$view['props'],
					'back'    => $view['props']['link'],
					'buttons' => fn () =>
						ViewButtons::view('site.preview', model: $site)
							->defaults(
								'site.versions',
								'languages'
							)
							->bind(['versionId' => $versionId])
							->render(),
					'src'       => $src,
					'versionId' => $versionId
				],
				'title' => I18n::translate('view.site') . ' | ' . I18n::translate('preview'),
			];
		}
	],
];
