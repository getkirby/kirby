<?php

return function () {
	return [
		'icon'    => 'palette',
		'label'   => 'Lab',
		'menu'    => false,
		'drawers' => require __DIR__ . '/lab/drawers.php',
		'views'   => require __DIR__ . '/lab/views.php'
	];
};
