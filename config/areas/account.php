<?php

use Kirby\Toolkit\I18n;

return function () {
	return [
		'icon'      => 'account',
		'label'     => I18n::translate('view.account'),
		'search'    => 'users',
		'buttons'   => require __DIR__ . '/account/buttons.php',
		'dialogs'   => require __DIR__ . '/account/dialogs.php',
		'drawers'   => require __DIR__ . '/account/drawers.php',
		'dropdowns' => require __DIR__ . '/account/dropdowns.php',
		'views'     => require __DIR__ . '/account/views.php'
	];
};
