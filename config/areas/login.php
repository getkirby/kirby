<?php

use Kirby\Panel\Controller\View\LoginViewController;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'  => 'user',
		'label' => I18n::translate('login'),
		'views' => [
			'login' => [
				'pattern' => 'login',
				'auth'    => false,
				'action'  => LoginViewController::class
			],
			'login.fallback' => [
				'pattern' => '(:all)',
				'auth'    => false,
				'action'  => function ($path) use ($kirby) {
					/**
					 * Store the current path in the session
					 * Once the user is logged in, the path will
					 * be used to redirect to that view again
					 */
					$kirby->session()->set('panel.path', $path);
					Panel::go(url: 'login', refresh: 0);
				}
			]
		]
	];
};
