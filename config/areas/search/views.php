<?php

use Kirby\Cms\App;
use Kirby\Panel\Panel;
use Kirby\Panel\View;

return [
	'search' => [
		'pattern' => 'search',
		'action'  => function () {
			return [
				'component' => 'k-search-view',
				'props' => [
					'type' => App::instance()->request()->get('type'),
				]
			];
		}
	],
];
