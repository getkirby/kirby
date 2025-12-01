<?php

use Kirby\Panel\Controller\View\BlueprintViewController;
use Kirby\Panel\Controller\View\BlueprintFieldViewController;
use Kirby\Panel\Controller\View\BlueprintFieldsViewController;
use Kirby\Panel\Controller\View\BlueprintTabViewController;
use Kirby\Panel\Controller\View\PageFileViewController;
use Kirby\Panel\Controller\View\PageViewController;
use Kirby\Panel\Controller\View\PreviewViewController;
use Kirby\Panel\Controller\View\RemotePreviewViewController;
use Kirby\Panel\Controller\View\SiteFileViewController;
use Kirby\Panel\Controller\View\SiteViewController;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'action'  => PageViewController::class
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'action'  => PageFileViewController::class
	],
	'page.preview' => [
		'pattern' => '(pages/.*?)/preview/(changes|latest|compare|form)',
		'action'  => PreviewViewController::class
	],
	'page.preview.remote' => [
		'pattern' => '(pages/.*?)/preview/(form)/remote',
		'action'  => RemotePreviewViewController::class
	],
	'page.blueprint' => [
		'pattern' => '(pages/.*?)/blueprint',
		'action'  => BlueprintViewController::class
	],
	'page.blueprint.tab' => [
		'pattern' => '(pages/.*?)/blueprint/tabs/(:any)',
		'action'  => BlueprintTabViewController::class
	],
	'page.blueprint.fields' => [
		'pattern' => '(pages/.*?)/blueprint/fields',
		'action'  => BlueprintFieldsViewController::class
	],
	'page.blueprint.field' => [
		'pattern' => '(pages/.*?)/blueprint/fields/(:any)',
		'action'  => BlueprintFieldViewController::class
	],
	'site' => [
		'pattern' => 'site',
		'action'  => SiteViewController::class
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'action'  => SiteFileViewController::class
	],
	'site.fields.blueprint' => [
		'pattern' => '(site)/fields/(:any)/blueprint',
		'action'  => BlueprintFieldViewController::class
	],
	'site.preview' => [
		'pattern' => '(site)/preview/(changes|latest|compare|form)',
		'action'  => PreviewViewController::class
	],
	'site.preview.remote' => [
		'pattern' => '(site)/preview/(form)/remote',
		'action'  => RemotePreviewViewController::class
	],
];
