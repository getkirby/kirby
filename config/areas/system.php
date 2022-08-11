<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'    => 'settings',
		'label'   => I18n::translate('view.system'),
		'menu'    => true,
		'dialogs' => require __DIR__ . '/system/dialogs.php',
		'views'   => require __DIR__ . '/system/views.php'
	];
};
