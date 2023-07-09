<?php

use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'    => 'key',
		'label'   => I18n::translate('license.register'),
		'menu'    => ['dialog' => 'registration'],
		'dialogs' => require __DIR__ . '/license/dialogs.php'
	];
};
