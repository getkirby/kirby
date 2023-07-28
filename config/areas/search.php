<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	if ($kirby->user()?->role()->permissions()->for('search') === true) {
		return [
			'icon'    => 'search',
			'label'   => I18n::translate('search'),
			'views'   => require __DIR__ . '/search/views.php'
		];
	}

	return [];
};
