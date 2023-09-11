<?php

use Kirby\Cms\App;
use Kirby\Panel\Panel;
use Kirby\Panel\View;

return [
	'search' => [
		'pattern' => 'search',
		'when'    => function (): bool {
			// checks core and custom search areas
			$permissions = App::instance()->user()?->role()->permissions()->toArray() ?? [];
			$searches    = View::searches(Panel::areas(), $permissions);

			return empty($searches) === false;
		},
		'action'  => function () {
			return [
				'component' => 'k-search-view',
				'props' => [
					'type' => App::instance()->request()->get('type') ?? 'pages',
				]
			];
		}
	],
];
