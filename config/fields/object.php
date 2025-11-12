<?php

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

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
		'default' => function (array|null $default = null) {
			return $default;
		},

		/**
		 * The placeholder text if no information has been added yet
		 */
		'empty' => function ($empty = null) {
			return I18n::translate($empty, $empty);
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
			if (empty($this->default) === true) {
				return '';
			}

			return $this->form($this->default)->toFormValues();
		},
		'fields' => function () {
			if ($this->fields === []) {
				return [];
			}

			return $this->form()->fields()->toProps();
		},
		'value' => function () {
			$data = Data::decode($this->value, 'yaml');

			if ($data === []) {
				return '';
			}

			return $this->form($data)->toFormValues();
		}
	],
	'methods' => [
		'form' => function (array $values = []): Form {
			$this->form ??= new Form(
				fields: $this->attrs['fields'],
				model: $this->model,
				language: 'current'
			);

			$this->form->reset();

			if ($values !== []) {
				$this->form->fill(
					input: $values,
					passthrough: true
				);
			}

			return $this->form;
		},
	],
	'save' => function ($value) {
		if ($this->isEmptyValue($value) === true) {
			return '';
		}

		return $this->form($value)->toStoredValues();
	},
	'validations' => [
		'object' => function ($value) {
			if (empty($value) === true) {
				return true;
			}

			$errors = $this->form($value)->errors();

			if ($errors !== []) {
				// use the first error for details
				$name  = array_key_first($errors);
				$error = $errors[$name];

				throw new InvalidArgumentException(
					key: 'object.validation',
					data: [
						'label'   => $error['label'] ?? $name,
						'message' => implode("\n", $error['message'])
					]
				);
			}
		}
	]
];
