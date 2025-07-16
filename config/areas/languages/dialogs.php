<?php

use Kirby\Panel\Ui\Dialogs\LanguageCreateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageDeleteDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationCreateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationDeleteDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationUpdateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageUpdateDialog;

return [
	'language.create' => [
		'pattern'    => 'languages/create',
		'controller' => LanguageCreateDialog::class
	],
	'language.delete' => [
		'pattern'    => 'languages/(:any)/delete',
		'controller' => LanguageDeleteDialog::class
	],
	'language.update' => [
		'pattern'    => 'languages/(:any)/update',
		'controller' => LanguageUpdateDialog::class
	],
	'language.translation.create' => [
		'pattern'    => 'languages/(:any)/translations/create',
		'controller' => LanguageTranslationCreateDialog::class
	],
	'language.translation.delete' => [
		'pattern'    => 'languages/(:any)/translations/(:any)/delete',
		'controller' => LanguageTranslationDeleteDialog::class
	],
	'language.translation.update' => [
		'pattern'    => 'languages/(:any)/translations/(:any)/update',
		'controller' => LanguageTranslationUpdateDialog::class
	]
];
