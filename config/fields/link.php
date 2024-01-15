<?php

use Kirby\Cms\Url;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;

return [
	'props' => [
		'after'       => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * @values 'anchor', 'url, 'page, 'file', 'email', 'tel', 'custom'
		 */
		'options' => function (array|null $options = null): array {
			return $options ?? [
				'url',
				'page',
				'file',
				'email',
				'tel',
				'anchor',
				'custom'
			];
		}
	],
	'computed' => [
		'value' => function () {
			$data = Data::decode($this->value, 'yaml');

			// support old string url syntax
			// @todo remove when string url syntax dropped
			if (empty($data[0]) === false) {
				return ['value' => $data[0]];
			}

			if (empty($data) === true) {
				return '';
			}

			return $data;
		}
	],
	'methods' => [
		'activeTypes' => function () {
			return array_filter(Url::availableLinkTypes(), function (string $type) {
				return in_array($type, $this->props['options']) === true;
			}, ARRAY_FILTER_USE_KEY);
		}
	],
	'save' => function ($value) {
		if (empty($value) === true) {
			return '';
		}

		return Data::encode($value, 'yaml');
	},
	'validations' => [
		'value' => function (array|string|null $link) {
			if (is_array($link) === false) {
				$link = ['value' => $link];
			}

			if (empty($link['value']) === true) {
				return true;
			}

			$detected = false;

			foreach ($this->activeTypes() as $type => $options) {
				if ($options['detect']($link['value']) !== true) {
					continue;
				}

				$previewLink = $options['link']($link['value']);
				$detected    = true;

				if ($options['validate']($previewLink) === false) {
					throw new InvalidArgumentException([
						'key' => 'validation.' . $type
					]);
				}
			}

			// none of the configured types has been detected
			if ($detected === false) {
				throw new InvalidArgumentException([
					'key' => 'validation.linkType'
				]);
			}

			return true;
		},
	]
];
