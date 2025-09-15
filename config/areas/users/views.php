<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Collector\UsersCollector;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\Panel\Ui\Item\UserItem;

return [
	'users' => [
		'pattern' => 'users',
		'action'  => function () {
			$kirby = App::instance();
			$role  = $kirby->request()->get('role');
			$roles = $kirby->roles()->toArray(fn ($role) => [
				'id'    => $role->id(),
				'title' => $role->title(),
			]);

			return [
				'component' => 'k-users-view',
				'props'     => [
					'buttons' => fn () =>
						ViewButtons::view('users')
							->defaults('create')
							->bind(['role' => $role])
							->render(),
					'role' => function () use ($roles, $role) {
						if ($role) {
							return $roles[$role] ?? null;
						}
					},
					'roles' => array_values($roles),
					'users' => function () use ($kirby, $role) {
						$collector = new UsersCollector(
							limit: 20,
							page: $kirby->request()->get('page', 1),
							role: $role,
							sortBy: 'username asc',
						);

						$users = $collector->models(paginated: true);

						return [
							'data'       => $users->values(fn ($user) => (new UserItem(user: $user))->props()),
							'pagination' => $users->pagination()->toArray()
						];
					},
				]
			];
		}
	],
	'user' => [
		'pattern' => 'users/(:any)',
		'action'  => function (string $id) {
			return Find::user($id)->panel()->view();
		}
	],
	'user.file' => [
		'pattern' => 'users/(:any)/files/(:any)',
		'action'  => function (string $id, string $filename) {
			return Find::file('users/' . $id, $filename)->panel()->view();
		}
	],
];
