<?php

use Kirby\Exception\Exception;
use Kirby\Form\Field;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
	'mixins' => ['datetime'],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'placeholder' => null,

		/**
		 * Activate/deactivate the dropdown calendar
		 */
		'calendar' => function (bool $calendar = true) {
			return $calendar;
		},

		/**
		 * Default date when a new page/file/user gets created
		 */
		'default' => function (string $default = null): string {
			return $this->toDatetime($default) ?? '';
		},

		/**
		 * Custom format (dayjs tokens: `DD`, `MM`, `YYYY`) that is
		 * used to display the field in the Panel
		 */
		'display' => function ($display = 'YYYY-MM-DD') {
			return I18n::translate($display, $display);
		},

		/**
		 * Changes the calendar icon to something custom
		 */
		'icon' => function (string $icon = 'calendar') {
			return $icon;
		},

		/**
		 * Latest date, which can be selected/saved (Y-m-d)
		 */
		'max' => function (string $max = null): string|null {
			return Date::optional($max);
		},
		/**
		 * Earliest date, which can be selected/saved (Y-m-d)
		 */
		'min' => function (string $min = null): string|null {
			return Date::optional($min);
		},

		/**
		 * Round to the nearest: sub-options for `unit` (day) and `size` (1)
		 */
		'step' => function ($step = null) {
			return $step;
		},

		/**
		 * Pass `true` or an array of time field options to show the time selector.
		 */
		'time' => function ($time = false) {
			return $time;
		},
		/**
		 * Must be a parseable date string
		 */
		'value' => function ($value = null) {
			return $value;
		}
	],
	'computed' => [
		'display' => function () {
			if ($this->display) {
				return Str::upper($this->display);
			}
		},
		'format' => function () {
			return $this->props['format'] ?? ($this->time === false ? 'Y-m-d' : 'Y-m-d H:i:s');
		},
		'time' => function () {
			if ($this->time === false) {
				return false;
			}

			$props = is_array($this->time) ? $this->time : [];
			$props['model'] = $this->model();
			$field = new Field('time', $props);
			return $field->toArray();
		},
		'step' => function () {
			if ($this->time === false || empty($this->time['step']) === true) {
				return Date::stepConfig($this->step, [
					'size' => 1,
					'unit' => 'day'
				]);
			}

			return Date::stepConfig($this->time['step'], [
				'size' => 5,
				'unit' => 'minute'
			]);
		},
		'value' => function (): string {
			return $this->toDatetime($this->value) ?? '';
		},
	],
	'validations' => [
		'date',
		'minMax' => function ($value) {
			if (!$value = Date::optional($value)) {
				return true;
			}

			$min = Date::optional($this->min);
			$max = Date::optional($this->max);

			$format = $this->time === false ? 'd.m.Y' : 'd.m.Y H:i';

			if ($min && $max && $value->isBetween($min, $max) === false) {
				throw new Exception([
					'key' => 'validation.date.between',
					'data' => [
						'min' => $min->format($format),
						'max' => $min->format($format)
					]
				]);
			} elseif ($min && $value->isMin($min) === false) {
				throw new Exception([
					'key' => 'validation.date.after',
					'data' => [
						'date' => $min->format($format),
					]
				]);
			} elseif ($max && $value->isMax($max) === false) {
				throw new Exception([
					'key' => 'validation.date.before',
					'data' => [
						'date' => $max->format($format),
					]
				]);
			}

			return true;
		},
	]
];
