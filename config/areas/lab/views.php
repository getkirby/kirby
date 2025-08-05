<?php

use Kirby\Panel\Controller\View\LabDocsViewController;
use Kirby\Panel\Controller\View\LabDocViewController;
use Kirby\Panel\Controller\View\LabExamplesViewController;
use Kirby\Panel\Controller\View\LabExampleViewController;
use Kirby\Panel\Controller\View\LabExampleVueViewController;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => LabExamplesViewController::class,
	],
	'lab.docs' => [
		'pattern' => 'lab/docs',
		'action'  => LabDocsViewController::class
	],
	'lab.doc' => [
		'pattern' => 'lab/docs/(:any)',
		'action'  => LabDocViewController::class
	],
	'lab.vue' => [
		'pattern' => [
			'lab/(:any)/(:any)/index.vue',
			'lab/(:any)/(:any)/(:any)/index.vue'
		],
		'action'  => LabExampleVueViewController::class
	],
	'lab.example' => [
		'pattern' => 'lab/(:any)/(:any)/(:any?)',
		'action'  => LabExampleViewController::class
	]
];
