<?php

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
	'mixins' => ['min'],
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
		 * Optional columns definition to only show selected fields in the structure table.
		 */
		'columns' => function (array $columns = []) {
			// lower case all keys, because field names will
			// be lowercase as well.
			return array_change_key_case($columns);
		},

		/**
		 * Toggles duplicating rows for the structure
		 */
		'duplicate' => function (bool $duplicate = true) {
			return $duplicate;
		},

		/**
		 * The placeholder text if no items have been added yet
		 */
		'empty' => function ($empty = null) {
			return I18n::translate($empty, $empty);
		},

		/**
		 * Set the default rows for the structure
		 */
		'default' => function (array|null $default = null) {
			return $default;
		},

		/**
		 * Fields setup for the structure form. Works just like fields in regular forms.
		 */
		'fields' => function (array $fields = []) {
			return $fields;
		},
		/**
		 * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
		 */
		'limit' => function (int|null $limit = null) {
			return $limit;
		},
		/**
		 * Maximum allowed entries in the structure. Afterwards the "Add" button will be switched off.
		 */
		'max' => function (int|null $max = null) {
			return $max;
		},
		/**
		 * Minimum required entries in the structure
		 */
		'min' => function (int|null $min = null) {
			return $min;
		},
		/**
		 * Toggles adding to the top or bottom of the list
		 */
		'prepend' => function (bool|null $prepend = null) {
			return $prepend;
		},
		/**
		 * Toggles drag & drop sorting
		 */
		'sortable' => function (bool|null $sortable = null) {
			return $sortable;
		},
		/**
		 * Sorts the entries by the given field and order (i.e. `title desc`)
		 * Drag & drop is disabled in this case
		 */
		'sortBy' => function (string|null $sort = null) {
			return $sort;
		}
	],
	'computed' => [
		'default' => function () {
			return $this->rows($this->default);
		},
		'value' => function () {
			return $this->rows($this->value);
		},
		'fields' => function () {
			if (empty($this->fields) === true) {
				return [];
			}

			return $this->form()->fields()->toArray();
		},
		'columns' => function () {
			$columns   = [];
			$blueprint = $this->columns;

			// if no custom columns have been defined,
			// gather all fields as columns
			if (empty($blueprint) === true) {
				// skip hidden fields
				$fields    = array_filter(
					$this->fields,
					fn ($field) =>
						$field['type'] !== 'hidden' && $field['hidden'] !== true
				);
				$fields    = array_column($fields, 'name');
				$blueprint = array_fill_keys($fields, true);
			}

			foreach ($blueprint as $name => $column) {
				$field = $this->fields[$name] ?? null;

				// Skip empty and unsaveable fields
				// They should never be included as column
				if (
					empty($field) === true ||
					$field['saveable'] === false
				) {
					continue;
				}

				if (is_array($column) === false) {
					$column = [];
				}

				$column['type']  ??= $field['type'];
				$column['label'] ??= $field['label'] ?? $name;
				$column['label']   = I18n::translate($column['label'], $column['label']);

				$columns[$name] = $column;
			}

			// make the first column visible on mobile
			// if no other mobile columns are defined
			if (in_array(true, array_column($columns, 'mobile'), true) === false) {
				$columns[array_key_first($columns)]['mobile'] = true;
			}

			return $columns;
		}
	],
	'methods' => [
		'rows' => function ($value) {
			$rows  = Data::decode($value, 'yaml');
			$form  = $this->form();
			$value = [];

			foreach ($rows as $index => $row) {
				if (is_array($row) === false) {
					continue;
				}

				$value[] = $form->reset()->fill(input: $row, passthrough: true)->toFormValues();
			}

			return $value;
		},
		'form' => function () {
			return new Form(
				fields: $this->attrs['fields'] ?? [],
				model: $this->model,
				language: 'current'
			);
		},
	],
	'save' => function ($value) {
		$data     = [];
		$form     = $this->form();
		$defaults = $form->defaults();

		foreach ($value as $index => $row) {
			$row = $form
				->reset()
				->fill(
					input: $defaults,
				)
				->submit(
					input: $row,
					passthrough: true
				)
				->toStoredValues();

			// remove frontend helper id
			unset($row['_id']);

			$data[] = $row;
		}

		return $data;
	},
	'validations' => [
		'min',
		'max',
		'structure' => function ($value) {
			if (empty($value) === true) {
				return true;
			}

			$values = A::wrap($value);
			$form   = $this->form();

			foreach ($values as $index => $value) {
				$form->reset()->submit(input: $value, passthrough: true);

				foreach ($form->fields() as $field) {
					$errors = $field->errors();

					if (empty($errors) === false) {
						throw new InvalidArgumentException(
							key: 'structure.validation',
							data: [
								'field' => $field->label() ?? Str::ucfirst($field->name()),
								'index' => $index + 1
							]
						);
					}
				}
			}
		}
	]
];
