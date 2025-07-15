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
		'pattern' => 'pages/(:any)/changeSort',
		'handler' => PageChangeSortDialog::for(...)
	],
	'page.changeStatus' => [
		'pattern' => 'pages/(:any)/changeStatus',
		'handler' => PageChangeStatusDialog::for(...)
	],
	'page.changeTemplate' => [
		'pattern' => 'pages/(:any)/changeTemplate',
		'handler' =>  PageChangeTemplateDialog::for(...)
	],
	'page.changeTitle' => [
		'pattern' => 'pages/(:any)/changeTitle',
		'handler' => PageChangeTitleDialog::for(...)
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
		'pattern' => 'pages/(:any)/delete',
		'handler' => PageDeleteDialog::for(...)
	],
	'page.duplicate' => [
		'pattern' => 'pages/(:any)/duplicate',
		'handler' => PageDuplicateDialog::for(...)
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
		'pattern' => 'pages/(:any)/move',
		'handler' => PageMoveDialog::for(...)
	],
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		'handler' => FieldDialog::forModel(...)
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
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDialog::forFile(...)
	],
	'site.changeTitle' => [
		'pattern' => 'site/changeTitle',
		'handler' => fn () => new SiteChangeTitleDialog()
	],
	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'handler' => FieldDialog::forModel(...)
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
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'handler' => FieldDialog::forFile(...)
	],
	'changes' => [
		'pattern' => 'changes',
		'handler' => fn () => new ChangesDialog()
	],
];
