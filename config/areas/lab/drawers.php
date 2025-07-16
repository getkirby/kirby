<?php

use Kirby\Panel\Ui\Drawers\LabsDocsDrawer;

return [
	'lab.docs' => [
		'pattern'    => 'lab/docs/(:any)',
		'controller' => LabsDocsDrawer::class
	],
];
