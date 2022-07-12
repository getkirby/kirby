<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Panel;

return [
	'account' => [
		'pattern' => 'account',
		'action'  => fn () => [
			'component' => 'k-account-view',
			'props'     => App::instance()->user()->panel()->props(),
		],
	],
	'account.file' => [
		'pattern' => 'account/files/(:any)',
		'action'  => function (string $filename) {
			return Find::file('account', $filename)->panel()->view();
		}
	],
	'account.logout' => [
		'pattern' => 'logout',
		'auth'    => false,
		'action'  => function () {
			if ($user = App::instance()->user()) {
				$user->logout();
			}
			Panel::go('login');
		},
	],
	'account.password' => [
		'pattern' => 'reset-password',
		'action'  => fn () => ['component' => 'k-reset-password-view']
	]
];
