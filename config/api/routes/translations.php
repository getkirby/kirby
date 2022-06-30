<?php

/**
 * Translations Routes
 */
return [
	[
		'pattern' => 'translations',
		'method'  => 'GET',
		'auth'    => false,
		'action'  => function () {
			return $this->kirby()->translations();
		}
	],
	[
		'pattern' => 'translations/(:any)',
		'method'  => 'GET',
		'auth'    => false,
		'action'  => function (string $code) {
			return $this->kirby()->translations()->find($code);
		}
	]

];
