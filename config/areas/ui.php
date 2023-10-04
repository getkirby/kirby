<?php

return function () {
	return [
		'icon'  => 'users',
		'label' => 'UI',
		'menu'  => false,
		'views' => require __DIR__ . '/ui/views.php'
	];
};
