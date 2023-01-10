<?php

use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'  => 'user',
		'label' => I18n::translate('logout'),
		'views' => [
			'logout' => [
				'pattern' => 'logout',
				'auth'    => false,
				'action'  => function () use ($kirby) {
					$kirby->auth()->logout();
					Panel::go('login');
				},
			]
		]
	];
};
