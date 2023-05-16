<?php

use Kirby\Uuid\Uuid;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
	'props' => [
		'value' => function (string $value = null) {
			return $value;
		}
	],
	'validations' => [
		'value' => function ($value) {
			if (Uuid::is($value, 'page') === true || Uuid::is($value, 'site') === true) {
				return true;
			}

			if (Str::startsWith($value, 'mailto:') === true) {
				if (V::email($value) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.email'
					]);
				}

				return true;
			}

			if (Url::isAbsolute($value) === true) {
				if (V::url($value) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.url'
					]);
				}

				return true;
			}
		},
	]
];
