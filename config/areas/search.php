<?php

use Kirby\Panel\Controller\View\SearchViewController;
use Kirby\Toolkit\I18n;

return function () {
	return [
		'icon'  => 'search',
		'label' => I18n::translate('search'),
		'views' => [
			'search' => [
				'pattern' => 'search',
				'action'  => SearchViewController::class
			]
		]
	];
};
