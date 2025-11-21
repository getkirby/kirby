<?php

use Kirby\Panel\Lab\Responses;

return [
	'lab.errors' => [
		'pattern' => 'lab/errors/(:any?)',
		'load'    => fn (string|null $type = null) => Responses::errorResponseByType($type)
	],
];
