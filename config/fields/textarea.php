<?php

return [
	'mixins' => ['filepicker', 'upload'],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'  => null,
		'before' => null,

		/**
		 * Enables/disables the format buttons. Can either be `true`/`false` or a list of allowed buttons. Available buttons: `headlines`, `italic`, `bold`, `link`, `email`, `file`, `code`, `ul`, `ol` (as well as `|` for a divider)
		 */
		'buttons' => function ($buttons = true) {
			return $buttons;
		},

		/**
		 * Enables/disables the character counter in the top right corner
		 */
		'counter' => function (bool $counter = true) {
			return $counter;
		},

		/**
		 * Sets the default text when a new page/file/user is created
		 */
		'default' => function (string|null $default = null) {
			return trim($default ?? '');
		},

		/**
		 * Sets the options for the files picker
		 */
		'files' => function ($files = []) {
			if (is_string($files) === true) {
				return ['query' => $files];
			}

			if (is_array($files) === false) {
				$files = [];
			}

			return $files;
		},

		/**
		 * Sets the font family (sans or monospace)
		 */
		'font' => function (string|null $font = null) {
			return $font === 'monospace' ? 'monospace' : 'sans-serif';
		},

		/**
		 * Maximum number of allowed characters
		 */
		'maxlength' => function (int|null $maxlength = null) {
			return $maxlength;
		},

		/**
		 * Minimum number of required characters
		 */
		'minlength' => function (int|null $minlength = null) {
			return $minlength;
		},

		/**
		 * Changes the size of the textarea. Available sizes: `small`, `medium`, `large`, `huge`
		 */
		'size' => function (string|null $size = null) {
			return $size;
		},

		/**
		 * If `false`, spellcheck will be switched off
		 */
		'spellcheck' => function (bool $spellcheck = true) {
			return $spellcheck;
		},

		'value' => function (string|null $value = null) {
			return trim($value ?? '');
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => 'files',
				'action' => function () {
					return $this->field()->filepicker([
						...$this->field()->files(),
						'page'   => $this->requestQuery('page'),
						'search' => $this->requestQuery('search')
					]);
				}
			],
			[
				'pattern' => 'upload',
				'method' => 'POST',
				'action' => function () {
					$field   = $this->field();
					$uploads = $field->uploads();

					return $this->field()->upload($this, $uploads, fn ($file, $parent) => [
						'filename' => $file->filename(),
						'dragText' => $file->panel()->dragText(
							absolute: $field->model()->is($parent) === false
						),
					]);
				}
			]
		];
	},
	'methods' => [
		'emptyValue' => function () {
			return '';
		}
	],
	'validations' => [
		'minlength',
		'maxlength'
	]
];
