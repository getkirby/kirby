<?php

return function () {
	return [
		'icon'     => 'lab',
		'label'    => 'Lab',
		'menu'     => false,
		'dialogs'  => require __DIR__ . '/lab/dialogs.php',
		'drawers'  => require __DIR__ . '/lab/drawers.php',
		'requests' => require __DIR__ . '/lab/requests.php',
		'views'    => require __DIR__ . '/lab/views.php'
	];
};
