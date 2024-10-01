<?php

use Kirby\Panel\Controller\Search;
use Kirby\Toolkit\I18n;

return [
	'users' => [
		'label' => I18n::translate('users'),
		'icon'  => 'users',
		'query' => fn (string|null $query, int $limit, int $page) => Search::users($query, $limit, $page)
	]
];
