<?php

use Kirby\Panel\Controller\SearchController;
use Kirby\Toolkit\I18n;

return [
	'pages' => [
		'label' => I18n::translate('pages'),
		'icon'  => 'page',
		'query' => fn (string|null $query, int $limit, int $page) => SearchController::pages($query, $limit, $page)
	],
	'files' => [
		'label' => I18n::translate('files'),
		'icon'  => 'image',
		'query' => fn (string|null $query, int $limit, int $page) => SearchController::files($query, $limit, $page)
	]
];
