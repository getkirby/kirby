<?php

use Kirby\Cms\App;
use Kirby\Panel\PageCreateDialog;
use Kirby\Panel\Ui\Dialogs\ChangesDialog;
use Kirby\Panel\Ui\Dialogs\FieldDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeSortDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeStatusDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeTemplateDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeTitleDialog;
use Kirby\Panel\Ui\Dialogs\PageDeleteDialog;
use Kirby\Panel\Ui\Dialogs\PageDuplicateDialog;
use Kirby\Panel\Ui\Dialogs\PageMoveDialog;
use Kirby\Panel\Ui\Dialogs\SiteChangeTitleDialog;

$files = require __DIR__ . '/../files/dialogs.php';

return [
	'page.changeSort' => [
		'pattern'    => 'pages/(:any)/changeSort',
		'controller' => PageChangeSortDialog::class
	],
	'page.changeStatus' => [
		'pattern'    => 'pages/(:any)/changeStatus',
		'controller' => PageChangeStatusDialog::class
	],
	'page.changeTemplate' => [
		'pattern'    => 'pages/(:any)/changeTemplate',
		'controller' => PageChangeTemplateDialog::class
	],
	'page.changeTitle' => [
		'pattern'    => 'pages/(:any)/changeTitle',
		'controller' => PageChangeTitleDialog::class
	],
	// create a new page
	// @deprecated 5.0.0 Use dialog route from pages section instead
	'page.create' => [
		'pattern' => 'pages/create',
		'load'    => function () {
			$request = App::instance()->request();
			$dialog  = new PageCreateDialog(
				parentId: $request->get('parent'),
				sectionId: $request->get('section'),
				slug: $request->get('slug'),
				template: $request->get('template'),
				title: $request->get('title'),
				viewId: $request->get('view'),
			);

			return $dialog->load();
		},
		'submit' => function () {
			$request = App::instance()->request();
			$dialog  = new PageCreateDialog(
				parentId: $request->get('parent'),
				sectionId: $request->get('section'),
				slug: $request->get('slug'),
				template: $request->get('template'),
				title: $request->get('title'),
				viewId: $request->get('view'),
			);

			return $dialog->submit($request->get());
		}
	],
	'page.delete' => [
		'pattern'    => 'pages/(:any)/delete',
		'controller' => PageDeleteDialog::class
	],
	'page.duplicate' => [
		'pattern'    => 'pages/(:any)/duplicate',
		'controller' => PageDuplicateDialog::class
	],

	// page field dialogs
	'page.fields' => [
		...$fields['model'],
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
	],

	// change filename
	'page.file.changeName' => [
		...$files['changeName'],
		'pattern' => '(pages/.*?)/files/(:any)/changeName',
	],

	// change sort
	'page.file.changeSort' => [
		...$files['changeSort'],
		'pattern' => '(pages/.*?)/files/(:any)/changeSort',
	],

	// change template
	'page.file.changeTemplate' => [
		...$files['changeTemplate'],
		'pattern' => '(pages/.*?)/files/(:any)/changeTemplate',
	],

	// delete
	'page.file.delete' => [
		...$files['delete'],
		'pattern' => '(pages/.*?)/files/(:any)/delete',
	],

	// page file field dialogs
	'page.file.fields' => [
		...$fields['file'],
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
	],

	// move page
	'page.move' => [
		'pattern'    => 'pages/(:any)/move',
		'controller' => PageMoveDialog::class
	],
	'page.fields' => [
		'pattern'    => '(pages/.*?)/fields/(:any)/(:all?)',
		'controller' => FieldDialog::class
	],
	'page.file.changeName' => [
		...$files['changeName'],
		'pattern' => '(pages/.*?)/files/(:any)/changeName',
	],
	'page.file.changeSort' => [
		...$files['changeSort'],
		'pattern' => '(pages/.*?)/files/(:any)/changeSort',
	],
	'page.file.changeTemplate' => [
		...$files['changeTemplate'],
		'pattern' => '(pages/.*?)/files/(:any)/changeTemplate',
	],
	'page.file.delete' => [
		...$files['delete'],
		'pattern' => '(pages/.*?)/files/(:any)/delete',
	],
	'page.file.fields' => [
		'pattern'    => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'controller' => FieldDialog::forFile(...)
	],
	'site.changeTitle' => [
		'pattern'    => 'site/changeTitle',
		'controller' => SiteChangeTitleDialog::class
	],
	'site.fields' => [
		'pattern'    => '(site)/fields/(:any)/(:all?)',
		'controller' => FieldDialog::forModel(...)
	],
	'site.file.changeName' => [
		...$files['changeName'],
		'pattern' => '(site)/files/(:any)/changeName',
	],
	'site.file.changeSort' => [
		...$files['changeSort'],
		'pattern' => '(site)/files/(:any)/changeSort',
	],
	'site.file.changeTemplate' => [
		...$files['changeTemplate'],
		'pattern' => '(site)/files/(:any)/changeTemplate',
	],
	'site.file.delete' => [
		...$files['delete'],
		'pattern' => '(site)/files/(:any)/delete',
	],
	'site.file.fields' => [
		'pattern'    => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'controller' => FieldDialog::forFile(...)
	],
	'changes' => [
		'pattern'    => 'changes',
		'controller' => ChangesDialog::class
	],
];
