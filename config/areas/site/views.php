<?php

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
	'site' => [
		'pattern' => 'site',
		'action'  => SiteViewController::class
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'action'  => SiteFileViewController::class
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
