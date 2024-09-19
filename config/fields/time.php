<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;

return [
	'mixins' => ['datetime'],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'placeholder' => null,

		/**
		 * Sets the default time when a new page/file/user is created
		 */
		'default' => function ($default = null): string|null {
			return $default;
		},

		/**
		 * Custom format (dayjs tokens: `HH`, `hh`, `mm`, `ss`, `a`) that is
		 * used to display the field in the Panel
		 */
		'display' => function ($display = null) {
			return I18n::translate($display, $display);
		},

		/**
		 * Changes the clock icon
		 */
		'icon' => function (string $icon = 'clock') {
			return $icon;
		},
		/**
		 * Latest time, which can be selected/saved (H:i or H:i:s)
		 */
		'max' => function (string|null $max = null): string|null {
			return Date::optional($max);
		},
		/**
		 * Earliest time, which can be selected/saved (H:i or H:i:s)
		 */
		'min' => function (string|null $min = null): string|null {
			return Date::optional($min);
		},

		/**
		 * `12` or `24` hour notation. If `12`, an AM/PM selector will be shown.
		 * If `display` is defined, that option will take priority.
		 */
		'notation' => function (int $value = 24) {
			return $value === 24 ? 24 : 12;
		},
		/**
		 * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
		 */
		'step' => function ($step = null) {
			return Date::stepConfig($step, [
				'size' => 5,
				'unit' => 'minute',
			]);
		},
		'value' => function ($value = null): string|null {
			return $value;
		}
	],
	'computed' => [
		'display' => function () {
			if ($this->display) {
				return $this->display;
			}

			return $this->notation === 24 ? 'HH:mm' : 'hh:mm a';
		},
		'default' => function (): string {
			return $this->toDatetime($this->default, 'H:i:s') ?? '';
		},
		'format' => function () {
			return $this->props['format'] ?? 'H:i:s';
		},
		'value' => function (): string|null {
			return $this->toDatetime($this->value, 'H:i:s') ?? '';
		}
	],
	'validations' => [
		'time',
		'minMax' => function ($value) {
			if (!$value = Date::optional($value)) {
				return true;
			}

			$min = Date::optional($this->min);
			$max = Date::optional($this->max);

			$format = 'H:i:s';

			if ($min && $max && $value->isBetween($min, $max) === false) {
				throw new InvalidArgumentException(
					key: 'validation.time.between',
					data: [
						'min' => $min->format($format),
						'max' => $min->format($format)
					]
				);
			}

			if ($min && $value->isMin($min) === false) {
				throw new InvalidArgumentException(
					key: 'validation.time.after',
					data: ['time' => $min->format($format)]
				);
			}

			if ($max && $value->isMax($max) === false) {
				throw new InvalidArgumentException(
					key: 'validation.time.before',
					data: ['time' => $max->format($format)]
				);
			}

			return true;
		},
	]
];
