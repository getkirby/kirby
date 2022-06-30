<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;

return [
	'props' => [
		/**
		 * Unset inherited props
		 */
		'placeholder' => null,

		/**
		 * Default value which will be saved when a new page/user/file is created
		 */
		'default' => function ($default = null) {
			return $this->default = $default;
		},
		/**
		 * Sets the text next to the toggle. The text can be a string or an array of two options. The first one is the negative text and the second one the positive. The text will automatically switch when the toggle is triggered.
		 */
		'text' => function ($value = null) {
			$model = $this->model();

			if (is_array($value) === true) {
				if (A::isAssociative($value) === true) {
					return $model->toSafeString(I18n::translate($value, $value));
				}

				foreach ($value as $key => $val) {
					$value[$key] = $model->toSafeString(I18n::translate($val, $val));
				}

				return $value;
			}

			if (empty($value) === false) {
				return $model->toSafeString(I18n::translate($value, $value));
			}

			return $value;
		},
	],
	'computed' => [
		'default' => function () {
			return $this->toBool($this->default);
		},
		'value' => function () {
			if ($this->props['value'] === null) {
				return $this->default();
			} else {
				return $this->toBool($this->props['value']);
			}
		}
	],
	'methods' => [
		'toBool' => function ($value) {
			return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
		}
	],
	'save' => function (): string {
		return $this->value() === true ? 'true' : 'false';
	},
	'validations' => [
		'boolean',
		'required' => function ($value) {
			if ($this->isRequired() && ($value === false || $this->isEmpty($value))) {
				throw new InvalidArgumentException(I18n::translate('field.required'));
			}
		},
	]
];
