<?php

use Kirby\Panel\Ui\Stats;

return [
	'mixins' => [
		'headline',
	],
	'props' => [
		/**
		 * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link`, `icon` and `theme` settings.
		 */
		'reports' => function (array|string|null $reports = null) {
			return $reports ?? [];
		},
		/**
		 * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
		 */
		'size' => function (string $size = 'large') {
			return $size;
		}
	],
	'computed' => [
		'stats' => function (): Stats {
			return $this->stats ??= Stats::from(
				model: $this->model(),
				reports: $this->reports(),
				size: $this->size()
			);
		},
		'reports' => function (): array {
			return $this->stats->reports();
		},
		'size' => function (): string {
			return $this->stats->size();
		}
	]
];
