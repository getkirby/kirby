<?php

use Kirby\Panel\Lab\Responses;

return [
	'lab.errors' => [
		'pattern' => 'requests/lab/errors/(:any?)',
		'action'  => fn (string|null $type = null) => Responses::errorResponseByType($type)
	],
	'lab.request' => [
		'pattern' => 'requests/lab/request/(:any?)',
		'method'  => 'GET|POST|DELETE',
		'action'  => fn (string|null $type = null) => Responses::requestResponseByType($type)
	],
];
