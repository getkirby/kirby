<?php

return function () {
	return [
		'icon'    => 'palette',
		'label'   => 'UI',
		'menu'    => false,
		'drawers' => require __DIR__ . '/ui/drawers.php',
		'views'   => require __DIR__ . '/ui/views.php'
	];
};
