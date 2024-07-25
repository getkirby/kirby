<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'    => 'translate',
		'label'   => I18n::translate('view.languages'),
		'menu'    => true,
		'buttons' => require __DIR__ . '/languages/buttons.php',
		'dialogs' => require __DIR__ . '/languages/dialogs.php',
		'views'   => require __DIR__ . '/languages/views.php'
	];
};
