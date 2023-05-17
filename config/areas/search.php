<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'    => 'search',
		'label'   => I18n::translate('search'),
		'menu'    => false,
		'views'   => require __DIR__ . '/search/views.php'
	];
};
