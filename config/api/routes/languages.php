<?php

/**
 * Roles Routes
 */
return [
	[
		'pattern' => 'languages',
		'method'  => 'GET',
		'action'  => function () {
			return $this->languages();
		}
	],
	[
		'pattern' => 'languages',
		'method'  => 'POST',
		'action'  => function () {
			return $this->languages()->create($this->requestBody());
		}
	],
	[
		'pattern' => 'languages/(:any)',
		'method'  => 'GET',
		'action'  => function (string $code) {
			return $this->languages()->find($code);
		}
	],
	[
		'pattern' => 'languages/(:any)',
		'method'  => 'PATCH',
		'action'  => function (string $code) {
			return $this->languages()->find($code)?->update($this->requestBody());
		}
	],
	[
		'pattern' => 'languages/(:any)',
		'method'  => 'DELETE',
		'action'  => function (string $code) {
			return $this->languages()->find($code)?->delete();
		}
	]
];
