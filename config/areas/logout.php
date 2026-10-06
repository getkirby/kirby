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

					// the Panel follows this redirect in place and has to
					// refresh the globals it cached, above all the CSRF token
					// that has just been removed by the logout
					Panel::go('login?_globals=$system,$translation');
				},
			]
		]
	];
};
