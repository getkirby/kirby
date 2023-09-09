<?php

use Kirby\Cms\App;

return [
	'search' => [
		'pattern' => 'search',
		'when'    => function (): bool {
			$kirby = App::instance();

			return (
				$kirby->user()
					?->role()
					->permissions()
					->for('access', 'site') === true ||
				$kirby->user()
					?->role()
					->permissions()
					->for('access', 'users') === true
			);
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
