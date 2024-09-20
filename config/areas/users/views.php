<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\Toolkit\Escape;

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
						$users = $kirby->users();

						if (empty($role) === false) {
							$users = $users->role($role);
						}

						// sort users alphabetically
						$users = $users->sortBy('username', 'asc');

						// paginate
						$users = $users->paginate([
							'limit' => 20,
							'page'  => $kirby->request()->get('page')
						]);

						return [
							'data' => $users->values(fn ($user) => [
								'id'    => $user->id(),
								'image' => $user->panel()->image(),
								'info'  => Escape::html($user->role()->title()),
								'link'  => $user->panel()->url(true),
								'text'  => Escape::html($user->username())
							]),
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
