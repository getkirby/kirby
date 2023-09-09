<?php

use Kirby\Cms\App;

return [
	'search' => [
		'pattern' => 'search',
		'when'    => function (): bool {
			if ($user = App::instance()->user()) {
				$permissions = $user->role()->permissions();

				return $permissions->for('access', 'site') === true ||
					$permissions->for('access', 'users') === true;
			}

			return false;
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
