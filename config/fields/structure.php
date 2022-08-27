<?php

use Kirby\Data\Data;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

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
		'default' => function (array $default = null) {
			return $default;
		},

		/**
		 * Fields setup for the structure form. Works just like fields in regular forms.
		 */
		'fields' => function (array $fields) {
			return $fields;
		},
		/**
		 * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
		 */
		'limit' => function (int $limit = null) {
			return $limit;
		},
		/**
		 * Maximum allowed entries in the structure. Afterwards the "Add" button will be switched off.
		 */
		'max' => function (int $max = null) {
			return $max;
		},
		/**
		 * Minimum required entries in the structure
		 */
		'min' => function (int $min = null) {
			return $min;
		},
		/**
		 * Toggles adding to the top or bottom of the list
		 */
		'prepend' => function (bool $prepend = null) {
			return $prepend;
		},
		/**
		 * Toggles drag & drop sorting
		 */
		'sortable' => function (bool $sortable = null) {
			return $sortable;
		},
		/**
		 * Sorts the entries by the given field and order (i.e. `title desc`)
		 * Drag & drop is disabled in this case
		 */
		'sortBy' => function (string $sort = null) {
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
				throw new Exception('Please provide some fields for the structure');
			}

			return $this->form()->fields()->toArray();
		},
		'columns' => function () {
			$columns = [];
			$mobile  = 0;

			if (empty($this->columns)) {
				foreach ($this->fields as $field) {
					// Skip hidden and unsaveable fields
					// They should never be included as column
					if ($field['type'] === 'hidden' || $field['saveable'] === false) {
						continue;
					}

					$columns[$field['name']] = [
						'type'  => $field['type'],
						'label' => $field['label'] ?? $field['name']
					];
				}
			} else {
				foreach ($this->columns as $columnName => $columnProps) {
					if (is_array($columnProps) === false) {
						$columnProps = [];
					}

					$field = $this->fields[$columnName] ?? null;

					if (empty($field) === true || $field['saveable'] === false) {
						continue;
					}

					if (($columnProps['mobile'] ?? false) === true) {
						$mobile++;
					}

					$columns[$columnName] = array_merge($columnProps, [
						'type'  => $field['type'],
						'label' => $field['label'] ?? $field['name']
					]);
				}
			}

			// make the first column visible on mobile
			// if no other mobile columns are defined
			if ($mobile === 0) {
				$columns[array_key_first($columns)]['mobile'] = true;
			}

			return $columns;
		}
	],
	'methods' => [
		'rows' => function ($value) {
			$rows  = Data::decode($value, 'yaml');
			$value = [];

			foreach ($rows as $index => $row) {
				if (is_array($row) === false) {
					continue;
				}

				$value[] = $this->form($row)->values();
			}

			return $value;
		},
		'form' => function (array $values = []) {
			return new Form([
				'fields' => $this->attrs['fields'],
				'values' => $values,
				'model'  => $this->model
			]);
		},
	],
	'api' => function () {
		return [
			[
				'pattern' => 'validate',
				'method'  => 'ALL',
				'action'  => function () {
					return array_values($this->field()->form($this->requestBody())->errors());
				}
			]
		];
	},
	'save' => function ($value) {
		$data = [];

		foreach ($value as $row) {
			$data[] = $this->form($row)->content();
		}

		return $data;
	},
	'validations' => [
		'min',
		'max'
	]
];
