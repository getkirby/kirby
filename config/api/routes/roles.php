<?php

/**
 * Roles Routes
 */
return [
	[
		'pattern' => 'roles',
		'method'  => 'GET',
		'action'  => function () {
			$kirby   = $this->kirby();
			$context = $kirby->request()->get('canBe');
			return $kirby->roles($context);
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
