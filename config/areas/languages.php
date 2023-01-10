<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'    => 'globe',
		'label'   => I18n::translate('view.languages'),
		'menu'    => true,
		'dialogs' => require __DIR__ . '/languages/dialogs.php',
		'views'   => require __DIR__ . '/languages/views.php'
	];
};
