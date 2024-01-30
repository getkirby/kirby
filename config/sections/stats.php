<?php

use Kirby\Toolkit\I18n;

return [
	'mixins' => [
		'headline',
	],
	'props' => [
		/**
		 * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link`, `icon` and `theme` settings.
		 */
		'reports' => function ($reports = null) {
			if ($reports === null) {
				return [];
			}

			if (is_string($reports) === true) {
				$reports = $this->model()->query($reports);
			}

			if (is_array($reports) === false) {
				return [];
			}

			return $reports;
		},
		/**
		 * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
		 */
		'size' => function (string $size = 'large') {
			return $size;
		}
	],
	'computed' => [
		'reports' => function () {
			$reports  = [];
			$model    = $this->model();
			$toString = fn ($value) => $value === null ? null : $model->toString($value);

			foreach ($this->reports as $report) {
				if (is_string($report) === true) {
					$report = $model->query($report);
				}

				if (is_array($report) === false) {
					continue;
				}

				$info  = $report['info'] ?? null;
				$label = $report['label'] ?? null;
				$link  = $report['link'] ?? null;
				$value = $report['value'] ?? null;

				$reports[] = [
					'icon'  => $toString($report['icon'] ?? null),
					'info'  => $toString(I18n::translate($info, $info)),
					'label' => $toString(I18n::translate($label, $label)),
					'link'  => $toString(I18n::translate($link, $link)),
					'theme' => $toString($report['theme'] ?? null),
					'value' => $toString(I18n::translate($value, $value))
				];
			}

			return $reports;
		}
	]
];
