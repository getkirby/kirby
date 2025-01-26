<?php

use Kirby\Data\Data;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

return [
	'mixins'      => ['min'],
	'props'       => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'autofocus'   => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Set the default rows for the structure
		 */
		'default'     => function (array $default = null) {
			return $default;
		},

		/**
		 * The placeholder text if no items have been added yet
		 */
		'empty'       => function ($empty = null) {
			return I18n::translate($empty, $empty);
		},

		/**
		 * Field setup for the repeatable input
		 */
		'field'       => function (array|string $attrs = null) {
			$allowed = [
				"color",
				"date",
				"list",
				"multiselect",
				"number",
				"range",
				"select",
				"slug",
				"tags",
				"tel",
				"text",
				"textarea",
				"time",
				"url",
				"writer",
			];

			if (is_string($attrs)) {
				$attrs = ['type' => $attrs];
			}

			$attrs ??= ['type' => 'text'];

			if (in_array($attrs['type'], $allowed) === false) {
				throw new InvalidArgumentException(
					message: $attrs['type'] . ' field type is not supported for the entries field.'
				);
			}

			return $attrs;
		},

		/**
		 * Maximum allowed entries in the field. Afterward the "Add" button will be switched off.
		 */
		'max'         => function (int $max = null) {
			return $max;
		},

		/**
		 * Minimum required entries in the field
		 */
		'min'         => function (int $min = null) {
			return $min;
		},

		/**
		 * Toggles drag & drop sorting
		 */
		'sortable'    => function (bool|null $sortable = null) {
			return $sortable;
		}
	],
	'computed'    => [
		'field' => function () {
			if (empty($this->field) === true) {
				return [];
			}

			return $this->form()->fields()->first()->toArray();
		},
		'value' => function () {
			return $this->entries($this->value);
		}
	],
	'methods'     => [
		'entries' => function ($value) {
			return Data::decode($value, 'yaml');
		},
		'form'    => function (array $values = []) {
			return new Form([
				'fields' => [$this->field],
				'values' => $values,
				'model'  => $this->model
			]);
		},
	],
	'save'        => function ($value = null) {
		return Data::encode($value, 'yaml');
	},
	'validations' => [
		'min',
		'max'
	],
];
