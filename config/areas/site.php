<?php

use Kirby\Cms\App;
use Kirby\Panel\Menu;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return function ($kirby) {
	return [
		'breadcrumbLabel' => function () use ($kirby) {
			return $kirby->site()->title()->or(I18n::translate('view.site'))->toString();
		},
		'icon'      => 'home',
		'label'     => $kirby->site()->blueprint()->title() ?? I18n::translate('view.site'),
		'menu'      => true,
		'current'   => function (string|null $id = null) {
			if ($id !== 'site') {
				return false;
			}

			// ensure that site menu entry is not shown as current
			// when a custom Panel menu link is active
			$path = App::instance()->request()->path()->toString();

			foreach (Menu::$links as $page) {
				if (Str::contains($path, $page['link']) === true) {
					return false;
				}
			}

			return true;
		},
		'dialogs'   => require __DIR__ . '/site/dialogs.php',
		'drawers'   => require __DIR__ . '/site/drawers.php',
		'dropdowns' => require __DIR__ . '/site/dropdowns.php',
		'requests'  => require __DIR__ . '/site/requests.php',
		'searches'  => require __DIR__ . '/site/searches.php',
		'views'     => require __DIR__ . '/site/views.php',
	];
};
