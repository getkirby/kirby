<?php

use Kirby\Cms\License;
use Kirby\Exception\PermissionException;

/**
 * Page
 */
return [
	'fields' => [
		'status' => function (License $license) {
			try {
				$this->validateAreaAccess('system');
				return $license->status()->value();
			} catch (PermissionException) {
				return null;
			}
		},
		'code' => function (License $license) {
			try {
				$this->validateAreaAccess('system');
				return $this->kirby()->user()->isAdmin() ? $license->code() : $license->code(true);
			} catch (PermissionException) {
				return null;
			}
		},
		'type' => function (License $license) {
			try {
				$this->validateAreaAccess('system');
				return $license->type()->label();
			} catch (PermissionException) {
				return null;
			}
		}
	],
	'type' => License::class,
];
