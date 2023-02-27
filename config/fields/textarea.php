<?php

use Kirby\Parsley\Parsley;
use Kirby\Parsley\Schema\Markdown;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Xml;

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
		'default' => function (string $default = null) {
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
		'font' => function (string $font = null) {
			return $font === 'monospace' ? 'monospace' : 'sans-serif';
		},

		/**
		 * Maximum number of allowed characters
		 */
		'maxlength' => function (int $maxlength = null) {
			return $maxlength;
		},

		/**
		 * Minimum number of required characters
		 */
		'minlength' => function (int $minlength = null) {
			return $minlength;
		},

		/**
		 * Changes the size of the textarea. Available sizes: `small`, `medium`, `large`, `huge`
		 */
		'size' => function (string $size = null) {
			return $size;
		},

		/**
		 * If `false`, spellcheck will be switched off
		 */
		'spellcheck' => function (bool $spellcheck = true) {
			return $spellcheck;
		},

		'value' => function (string $value = null) {
			return trim($value ?? '');
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => 'files',
				'action' => function () {
					$params = array_merge($this->field()->files(), [
						'page'   => $this->requestQuery('page'),
						'search' => $this->requestQuery('search')
					]);

					return $this->field()->filepicker($params);
				}
			],
			[
				'pattern' => 'paste',
				'method'  => 'POST',
				'action' => function () {
					$request  = $this->kirby()->request();
					$parser   = new Parsley($request->get('html'), new Markdown());
					$blocks   = array_column($parser->blocks(), 'content');
					$markdown = implode(PHP_EOL . PHP_EOL, $blocks);
					$markdown = html_entity_decode($markdown);

					return [
						'markdown' => $markdown
					];
				}
			],
			[
				'pattern' => 'upload',
				'method' => 'POST',
				'action' => function () {
					$field   = $this->field();
					$uploads = $field->uploads();

					return $this->field()->upload($this, $uploads, function ($file, $parent) use ($field) {
						$absolute = $field->model()->is($parent) === false;

						return [
							'filename' => $file->filename(),
							'dragText' => $file->panel()->dragText('auto', $absolute),
						];
					});
				}
			]
		];
	},
	'validations' => [
		'minlength',
		'maxlength'
	]
];
