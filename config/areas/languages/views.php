<?php

use Kirby\Cms\App;
use Kirby\Panel\Controller\View\LanguagesViewController;
use Kirby\Panel\Controller\View\LanguageViewController;

return [
	'language' => [
		'pattern' => 'languages/(:any)',
		'when'    => fn (): bool =>
			App::instance()->option('languages.variables', true) !== false,
		'action'  => LanguageViewController::class
	],
	'languages' => [
		'pattern' => 'languages',
		'action'  => LanguagesViewController::class
	]
];
