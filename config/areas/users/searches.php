<?php

use Kirby\Panel\Controller\SearchController;
use Kirby\Toolkit\I18n;

return [
	'users' => [
		'label' => I18n::translate('users'),
		'icon'  => 'users',
		'query' => fn (string|null $query, int $limit, int $page) => SearchController::users($query, $limit, $page)
	]
];
