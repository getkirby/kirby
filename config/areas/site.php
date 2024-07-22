<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	$blueprint = $kirby->site()->blueprint();

	return [
		'breadcrumbLabel' => function () use ($kirby) {
			return $kirby->site()->title()->or(I18n::translate('view.site'))->toString();
		},
		'icon'      => $blueprint->icon() ?? 'home',
		'label'     => $blueprint->title() ?? I18n::translate('view.site'),
		'menu'      => true,
		'dialogs'   => require __DIR__ . '/site/dialogs.php',
		'drawers'   => require __DIR__ . '/site/drawers.php',
		'dropdowns' => require __DIR__ . '/site/dropdowns.php',
		'requests'  => require __DIR__ . '/site/requests.php',
		'searches'  => require __DIR__ . '/site/searches.php',
		'views'     => require __DIR__ . '/site/views.php',
	];
};
