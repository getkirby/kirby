<?php

use Kirby\Panel\Ui\Drawers\LabsDocsDrawer;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'handler' => LabsDocsDrawer::for(...)
	],
];
