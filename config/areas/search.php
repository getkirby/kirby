<?php

use Kirby\Toolkit\I18n;

return function () {
	return [
		'icon'    => 'search',
		'label'   => I18n::translate('search'),
		'views'   => require __DIR__ . '/search/views.php'
	];
};
