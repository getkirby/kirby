<?php

use Kirby\Panel\Controller\Request\FileItemsRequestController;
use Kirby\Panel\Controller\Request\PageItemsRequestController;
use Kirby\Panel\Controller\Request\PageTreeParentsRequestController;
use Kirby\Panel\Controller\Request\PageTreeRequestController;
use Kirby\Panel\Controller\Request\UserItemsRequestController;

return [
	'items.files' => [
		'pattern' => 'items/files',
		'action'  => FileItemsRequestController::class
	],
	'items.pages' => [
		'pattern' => 'items/pages',
		'action'  => PageItemsRequestController::class
	],
	'items.users' => [
		'pattern' => 'items/users',
		'action'  => UserItemsRequestController::class
	],
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => PageTreeRequestController::class
	],
	'tree.parents' => [
		'pattern' => 'site/tree/parents',
		'action'  => PageTreeParentsRequestController::class
	]
];
