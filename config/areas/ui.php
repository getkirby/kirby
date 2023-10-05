<?php

return function () {
	return [
		'icon'  => 'palette',
		'label' => 'UI',
		'menu'  => false,
		'views' => require __DIR__ . '/ui/views.php'
	];
};
