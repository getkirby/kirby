<?php

use Kirby\Panel\Controller\Search\FilesSearchController;
use Kirby\Panel\Controller\Search\PagesSearchController;
use Kirby\Toolkit\I18n;

return [
	'pages' => [
		'label'  => I18n::translate('pages'),
		'icon'   => 'page',
		'action' => PagesSearchController::class
	],
	'files' => [
		'label'  => I18n::translate('files'),
		'icon'   => 'image',
		'action' => FilesSearchController::class
	]
];
