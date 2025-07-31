<?php

use Kirby\Panel\Controller\Request\PageTreeParentsRequestController;
use Kirby\Panel\Controller\Request\PageTreeRequestController;

return [
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => PageTreeRequestController::class
	],
	'tree.parents' => [
		'pattern' => 'site/tree/parents',
		'action'  => PageTreeParentsRequestController::class
	]
];
