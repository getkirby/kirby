<?php

use Kirby\Panel\Controller\View\FileViewController;
use Kirby\Panel\Controller\View\ModelPreviewViewController;
use Kirby\Panel\Controller\View\PageViewController;
use Kirby\Panel\Controller\View\SiteViewController;

return [
	'page' => [
		'pattern' => 'pages/(:any)',
		'action'  => PageViewController::class
	],
	'page.file' => [
		'pattern' => '(pages/.*?)/files/(:any)',
		'action'  => FileViewController::class
	],
	'page.preview' => [
		'pattern' => '(pages/.*?)/preview/(changes|latest|compare)',
		'action'  => ModelPreviewViewController::class
	],
	'site' => [
		'pattern' => 'site',
		'action'  => SiteViewController::class
	],
	'site.file' => [
		'pattern' => '(site)/files/(:any)',
		'action'  => FileViewController::class
	],
	'site.preview' => [
		'pattern' => '(site)/preview/(changes|latest|compare)',
		'action'  => ModelPreviewViewController::class
	],
];
