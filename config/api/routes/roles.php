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

			switch ($kirby->request()->get('canBe')) {
				case 'changed':
					return $kirby->roles()->canBeChanged();
				case 'created':
					return $kirby->roles()->canBeCreated();
				default:
					return $kirby->roles();
			}
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
