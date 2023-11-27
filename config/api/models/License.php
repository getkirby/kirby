<?php

use Kirby\Cms\License;

/**
 * Page
 */
return [
	'fields' => [
		'status' => fn (License $license) => $license->status()->value(),
		'code'   => function (License $license) {
			return $this->kirby()->user()->isAdmin() ? $license->code() : $license->code(true);
		},
		'type' => fn (License $license) => $license->type()->label(),
	],
	'type' => License::class,
];
