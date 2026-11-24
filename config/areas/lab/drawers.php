<?php

use Kirby\Panel\Controller\Drawer\LabDocDrawerController;
use Kirby\Panel\Lab\Responses;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'action'  => LabDocDrawerController::class
	],
	'lab.errors' => [
		'pattern' => 'lab/errors/(:any?)',
		'load'    => fn (string|null $type = null) => Responses::errorResponseByType($type)
	],
];
