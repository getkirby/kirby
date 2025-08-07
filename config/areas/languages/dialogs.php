<?php

use Kirby\Panel\Controller\Dialog\LanguageDeleteDialogController;
use Kirby\Panel\Controller\Dialog\LanguageFormDialogController;
use Kirby\Panel\Controller\Dialog\LanguageVariableDeleteDialogController;
use Kirby\Panel\Controller\Dialog\LanguageVariableFormDialogController;

return [
	'language.create' => [
		'pattern' => 'languages/create',
		'action'  => LanguageFormDialogController::class
	],
	'language.delete' => [
		'pattern' => 'languages/(:any)/delete',
		'action'  => LanguageDeleteDialogController::class
	],
	'language.update' => [
		'pattern' => 'languages/(:any)/update',
		'action'  => LanguageFormDialogController::class
	],

	'language.translation.create' => [
		'pattern' => 'languages/(:any)/translations/create',
		'action'  => LanguageVariableFormDialogController::class
	],
	'language.translation.delete' => [
		'pattern' => 'languages/(:any)/translations/(:any)/delete',
		'action'  => LanguageVariableDeleteDialogController::class
	],
	'language.translation.update' => [
		'pattern' => 'languages/(:any)/translations/(:any)/update',
		'action'  => LanguageVariableFormDialogController::class
	]
];
