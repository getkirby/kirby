<?php

/**
 * Roles Routes
 */
return [
	[
		'pattern' => 'roles',
		'method'  => 'GET',
		'action'  => function () {
			$kirby = $this->kirby();

			return match ($kirby->request()->get('canBe')) {
				'changed' => $kirby->roles()->canBeChanged(),
				'created' => $kirby->roles()->canBeCreated(),
				default   => $kirby->roles()
			};
		}
	],
	[
		'pattern' => 'roles/(:any)',
		'method'  => 'GET',
		'action'  => function (string $name) {
			return $this->kirby()->roles()->find($name);
		}
	]
];
