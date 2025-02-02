<?php

use Kirby\Cms\App;
use Kirby\Panel\Ui\Dialogs\ChangesDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeSortDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeStatusDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeTemplateDialog;
use Kirby\Panel\Ui\Dialogs\PageChangeTitleDialog;
use Kirby\Panel\PageCreateDialog;
use Kirby\Panel\Ui\Dialogs\PageDeleteDialog;
use Kirby\Panel\Ui\Dialogs\PageDuplicateDialog;
use Kirby\Panel\Ui\Dialogs\PageMoveDialog;
use Kirby\Panel\Ui\Dialogs\SiteChangeTitleDialog;

$fields = require __DIR__ . '/../fields/dialogs.php';
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
	'page.move' => [
		'pattern' => 'pages/(:any)/move',
		'handler' => PageMoveDialog::for(...)
	],
	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'page.file.changeName' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeName',
		...$files['changeName']
	],
	'page.file.changeSort' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeSort',
		...$files['changeSort']
	],
	'page.file.changeTemplate' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeTemplate',
		...$files['changeTemplate']
	],
	'page.file.delete' => [
		'pattern' => '(pages/.*?)/files/(:any)/delete',
		...$files['delete']
	],
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	],
	'site.changeTitle' => [
		'pattern' => 'site/changeTitle',
		'handler' => fn () => new SiteChangeTitleDialog()
	],
	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		...$fields['model']
	],
	'site.file.changeName' => [
		'pattern' => '(site)/files/(:any)/changeName',
		...$files['changeName']
	],
	'site.file.changeSort' => [
		'pattern' => '(site)/files/(:any)/changeSort',
		...$files['changeSort']
	],
	'site.file.changeTemplate' => [
		'pattern' => '(site)/files/(:any)/changeTemplate',
		...$files['changeTemplate']
	],
	'site.file.delete' => [
		'pattern' => '(site)/files/(:any)/delete',
		...$files['delete']
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		...$fields['file']
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/sections/(:any)/(:all?)',
		...$sections['file']
	],
	'changes' => [
		'pattern' => 'changes',
		'handler' => fn () => new ChangesDialog()
	],
];
