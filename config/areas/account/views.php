<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\I18n;

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
	'account.password' => [
		'pattern' => 'reset-password',
		'action'  => fn () => [
			'component' => 'k-reset-password-view',
			'breadcrumb' => [
				[
					'label' => I18n::translate('view.resetPassword')
				]
			]
		]
	]
];
