<?php

use Kirby\Cms\Find;

/**
 * Roles Routes
 */
return [
	[
		'pattern' => 'roles',
		'method'  => 'GET',
		'action'  => function () {
			return match ($this->requestQuery('canBe')) {
				'changed' => Find::roles()->canBeChanged(),
				'created' => Find::roles()->canBeCreated(),
				default   => Find::roles()
			};
		}
	],
	[
		'pattern' => 'roles/(:any)',
		'method'  => 'GET',
		'action'  => function (string $name) {
			return Find::role($name);
		}
	]
];
