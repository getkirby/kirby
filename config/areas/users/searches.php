<?php

use Kirby\Panel\Controller\Search\UsersSearchController;
use Kirby\Toolkit\I18n;

return [
	'users' => [
		'label'  => I18n::translate('users'),
		'icon'   => 'users',
		'action' => UsersSearchController::class
	]
];
