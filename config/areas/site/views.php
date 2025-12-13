<?php

use Kirby\Panel\Controller\View\PageFileViewController;
use Kirby\Panel\Controller\View\PagePreviewViewController;
use Kirby\Panel\Controller\View\PageViewController;
use Kirby\Panel\Controller\View\SiteFileViewController;
use Kirby\Panel\Controller\View\SitePreviewViewController;
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
		'action'  => PagePreviewViewController::class
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
		'action'  => SitePreviewViewController::class
	],
];
