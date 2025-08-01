<?php

use Kirby\Panel\Controller\Drawer\LabDocDrawerController;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'action'  => LabDocDrawerController::class
	]
];
