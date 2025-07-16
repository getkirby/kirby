<?php

use Kirby\Panel\Ui\Dialogs\FileChangeNameDialog;
use Kirby\Panel\Ui\Dialogs\FileChangeSortDialog;
use Kirby\Panel\Ui\Dialogs\FileChangeTemplateDialog;
use Kirby\Panel\Ui\Dialogs\FileDeleteDialog;

/**
 * Shared file dialogs
 * They are included in the site and
 * users area to create dialogs there.
 * The array keys are replaced by
 * the appropriate routes in the areas.
 */
return [
	'changeName' => [
		'controller' => FileChangeNameDialog::class
	],
	'changeSort' => [
		'controller' => FileChangeSortDialog::class
	],
	'changeTemplate' => [
		'controller' => FileChangeTemplateDialog::class
	],
	'delete' => [
		'controller' => FileDeleteDialog::class
	]
];
