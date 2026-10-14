<?php

use Kirby\Panel\Controller\Request\LoginRequestController;
use Kirby\Panel\Controller\View\LoginViewController;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'     => 'user',
		'label'    => I18n::translate('login'),
		'requests' => [
			'login' => [
				'pattern' => 'login/(:any)/(:any)',
				'method'  => 'POST',
				'auth'    => false,
				'action'  => LoginRequestController::class
			]
		],
		'views' => [
			'login' => [
				'pattern' => 'login',
				'auth'    => false,
				'action'  => function () use ($kirby) {
					$method = $kirby->auth()->methods()->firstEnabled();
					$query  = $kirby->request()->query();
					$path   = 'login/method/' . $method::type();

					// keep the query, e.g. the globals the logout view
					// requests, as this is only a redirect to the method
					if ($query->isNotEmpty() === true) {
						$path .= '?' . $query->toString();
					}

					Panel::go($path);
				}
			],
			'login.view' => [
				'pattern' => 'login/(:any)/(:any)',
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
