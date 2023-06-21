<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

return [
	'props' => [
		'after'  => null,
		'before' => null,
		'icon'   => null,

		'value' => function (string|null $value = null) {
			return $value ?? '';
		}
	],
	'validations' => [
		'value' => function ($value) {
			if (V::uuid($value) === true) {
				return true;
			}

			if (Str::startsWith($value, 'mailto:') === true) {
				// get the plain email address
				$email = str_replace('mailto:', '', $value);

				// validate the email address
				if (V::email($email) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.email'
					]);
				}

				return true;
			}

			if (Str::startsWith($value, 'tel:') === true) {
				// get the plain phone number
				$tel = str_replace('tel:', '', $value);

				// validate the phone address
				if (V::tel($tel) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.tel'
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
