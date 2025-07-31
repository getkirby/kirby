<?php

use Kirby\Cms\App;
use Kirby\Panel\Controller\Dialog\ChangesDialogController;
use Kirby\Panel\Controller\Dialog\FieldDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeNameDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeSortDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeTemplateDialogController;
use Kirby\Panel\Controller\Dialog\FileDeleteDialogController;
use Kirby\Panel\Controller\Dialog\PageChangeSortDialogController;
use Kirby\Panel\Controller\Dialog\PageChangeStatusDialogController;
use Kirby\Panel\Controller\Dialog\PageChangeTemplateDialogController;
use Kirby\Panel\Controller\Dialog\PageChangeTitleDialogController;
use Kirby\Panel\Controller\Dialog\PageDeleteDialogController;
use Kirby\Panel\Controller\Dialog\PageDuplicateDialogController;
use Kirby\Panel\Controller\Dialog\PageMoveDialogController;
use Kirby\Panel\Controller\Dialog\SiteChangeTitleDialogController;
use Kirby\Panel\Ui\Dialogs\PageCreateDialog;

return [
	'page.changeSort' => [
		'pattern' => 'pages/(:any)/changeSort',
		'action'  => PageChangeSortDialogController::class
	],
	'page.changeStatus' => [
		'pattern' => 'pages/(:any)/changeStatus',
		'action'  => PageChangeStatusDialogController::class
	],
	'page.changeTemplate' => [
		'pattern' => 'pages/(:any)/changeTemplate',
		'action'  => PageChangeTemplateDialogController::class
	],
	'page.changeTitle' => [
		'pattern' => 'pages/(:any)/changeTitle',
		'action' => PageChangeTitleDialogController::class
	],
	'page.create' => [
		'pattern' => 'pages/create',
		'load' => function () {
			$request = App::instance()->request();
			$dialog  = new PageCreateDialog(
				parentId: $request->get('parent'),
				sectionId: $request->get('section'),
				slug: $request->get('slug'),
				template: $request->get('template'),
				title: $request->get('title'),
				uuid: $request->get('uuid'),
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
				uuid: $request->get('uuid'),
				viewId: $request->get('view'),
			);

			return $dialog->submit($request->get());
		}
	],
	'page.delete' => [
		'pattern' => 'pages/(:any)/delete',
		'action' => PageDeleteDialogController::class
	],
	'page.duplicate' => [
		'pattern' => 'pages/(:any)/duplicate',
		'action' => PageDuplicateDialogController::class
	],
	'page.move' => [
		'pattern' => 'pages/(:any)/move',
		'action'  => PageMoveDialogController::class
	],

	'page.fields' => [
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],
	'page.file.changeName' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeName',
		'action' => FileChangeNameDialogController::class
	],
	'page.file.changeSort' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeSort',
		'action' => FileChangeSortDialogController::class
	],
	'page.file.changeTemplate' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeTemplate',
		'action' => FileChangeTemplateDialogController::class
	],
	'page.file.delete' => [
		'pattern' => '(pages/.*?)/files/(:any)/delete',
		'action' => FileDeleteDialogController::class
	],
	'page.file.fields' => [
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],

	'site.changeTitle' => [
		'pattern' => 'site/changeTitle',
		'action'  => SiteChangeTitleDialogController::class,
	],

	'site.fields' => [
		'pattern' => '(site)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],
	'site.file.changeName' => [
		'pattern' => '(site)/files/(:any)/changeName',
		'action' => FileChangeNameDialogController::class
	],
	'site.file.changeSort' => [
		'pattern' => '(site)/files/(:any)/changeSort',
		'action' => FileChangeSortDialogController::class
	],
	'site.file.changeTemplate' => [
		'pattern' => '(site)/files/(:any)/changeTemplate',
		'action' => FileChangeTemplateDialogController::class
	],
	'site.file.delete' => [
		'pattern' => '(site)/files/(:any)/delete',
		'action' => FileDeleteDialogController::class
	],
	'site.file.fields' => [
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
		'action'  => FieldDialogController::class
	],

	'changes' => [
		'pattern' => 'changes',
		'action'  => ChangesDialogController::class,
	],
];
