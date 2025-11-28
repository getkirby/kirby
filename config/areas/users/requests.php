<?php

use Kirby\Panel\Controller\Request\UserItemsRequestController;

return [
	'items.users' => [
		'pattern' => 'items/users',
		'action'  => UserItemsRequestController::class
	]
];
