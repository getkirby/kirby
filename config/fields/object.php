<?php

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\A;

return [
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'autofocus'   => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Set the default values for the object
		 */
		'default' => function (array $default = []) {
			return $default;
		},

		/**
		 * Fields setup for the object form. Works just like fields in regular forms.
		 */
		'fields' => function (array $fields = []) {
			return $fields;
		}
	],
	'computed' => [
		'default' => function () {
			return $this->form($this->default)->values();
		},
		'fields' => function () {
			if (empty($this->fields) === true) {
				throw new Exception('Please provide some fields for the object');
			}

			return $this->form()->fields()->toArray();
		},
		'value' => function () {
			$data = Data::decode($this->value, 'yaml');

			return $this->form($data)->values();
		}
	],
	'methods' => [
		'form' => function (array $values = []) {
			return new Form([
				'fields' => $this->attrs['fields'],
				'values' => $values,
				'model'  => $this->model
			]);
		},
	],
	'save' => function ($value) {
		return $this->form($value)->content();
	},
	'validations' => [
		'object' => function ($value) {
			$errors = $this->form($value)->errors();

			if (empty($errors) === false) {
				// use the first error for details
				$error = A::first($errors);

				throw new InvalidArgumentException([
					'key'  => 'object.validation',
					'data' => [
						'label' => $error['label'],
						'message' => A::first($error['message'])
					]
				]);
			}
		}
	]
];
