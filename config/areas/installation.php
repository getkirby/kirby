<?php

use Kirby\Panel\Controller\View\InstallationViewController;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

return function ($kirby) {
	return [
		'icon'  => 'settings',
		'label' => I18n::translate('view.installation'),
		'views' => [
			'installation' => [
				'pattern' => 'installation',
				'auth'    => false,
				'action'  => InstallationViewController::class
			],
			'installation.fallback' => [
				'pattern' => '(:all)',
				'auth'    => false,
				'action'  => fn () => Panel::go('installation')
			]
		]
	];
};
