<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'      => 'users',
		'label'     => I18n::translate('view.users'),
		'search'    => 'users',
		'menu'      => true,
		'buttons'   => require __DIR__ . '/users/buttons.php',
		'dialogs'   => require __DIR__ . '/users/dialogs.php',
		'drawers'   => require __DIR__ . '/users/drawers.php',
		'dropdowns' => require __DIR__ . '/users/dropdowns.php',
		'requests'  => require __DIR__ . '/users/requests.php',
		'searches'  => require __DIR__ . '/users/searches.php',
		'views'     => require __DIR__ . '/users/views.php'
	];
};
