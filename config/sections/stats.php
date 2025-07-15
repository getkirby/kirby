<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Stat;

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
			$reports = [];
			$model   = $this->model();

			foreach ($this->reports as $report) {
				try {
					$stat = Stat::from(
						model: $model,
						input: $report
					);
				} catch (InvalidArgumentException) {
					continue;
				}

				$reports[] = $stat->props();
			}

			return $reports;
		}
	]
];
