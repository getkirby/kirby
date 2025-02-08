<?php

use Kirby\Panel\Ui\Dialogs\LanguageCreateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageDeleteDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationCreateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationDeleteDialog;
use Kirby\Panel\Ui\Dialogs\LanguageTranslationUpdateDialog;
use Kirby\Panel\Ui\Dialogs\LanguageUpdateDialog;

return [
	'language.create' => [
		'pattern' => 'languages/create',
		'handler' => fn () => new LanguageCreateDialog()
	],
	'language.delete' => [
		'pattern' => 'languages/(:any)/delete',
		'handler' => LanguageDeleteDialog::for(...)
	],
	'language.update' => [
		'pattern' => 'languages/(:any)/update',
		'handler' => LanguageUpdateDialog::for(...)
	],
	'language.translation.create' => [
		'pattern' => 'languages/(:any)/translations/create',
		'handler' => LanguageTranslationCreateDialog::for(...)
	],
	'language.translation.delete' => [
		'pattern' => 'languages/(:any)/translations/(:any)/delete',
		'handler' => LanguageTranslationDeleteDialog::for(...)
	],
	'language.translation.update' => [
		'pattern' => 'languages/(:any)/translations/(:any)/update',
		'handler' => LanguageTranslationUpdateDialog::for(...)
	]
];
