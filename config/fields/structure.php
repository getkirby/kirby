<?php

use Kirby\Form\Field\StructureField;

return [
	'mixins' => ['min'],
	'proxy' => function (...$attrs) {
		return StructureField::factory($attrs);
	},
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
		 * Whether to enable batch editing
		 */
		'batch' => function (bool $batch = false) {
			return $batch;
		},

		/**
		 * Optional columns definition to only show selected fields in the structure table.
		 */
		'columns' => function (array $columns = []) {
			return $columns;
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
			return $empty;
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
		'value' => function () {
			return $this->proxy->fill($this->value ?? [])->toFormValue();
		},
	],
	'save' => function ($value) {
		return $this->proxy->submit($value)->toStoredValue();
	},
	'validations' => [
		'min',
		'max',
		'structure' => fn ($value) => $this->proxy->validations()['structure']($value)
	]
];
