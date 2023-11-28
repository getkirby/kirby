<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

return [
	'users' => [
		'label' => I18n::translate('users'),
		'icon'  => 'users',
		'query' => function (string $query = null, int $limit, int $page) {
			$kirby = App::instance();
			$users = $kirby->users()
				->search($query)
				->paginate($limit, $page);

			return [
				'results' => $users->values(fn ($user) => [
					'image' => $user->panel()->image(),
					'text'  => Escape::html($user->username()),
					'link'  => $user->panel()->url(true),
					'info'  => Escape::html($user->role()->title()),
					'uuid'  => $user->uuid()->toString(),
				]),
				'pagination' => $users->pagination()->toArray()
			];
		}
	]
];
