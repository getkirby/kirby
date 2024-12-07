<?php

return [
	'props' => [
		/**
		 * Activates the batch delete option for the section
		 */
		'batch' => function (bool $batch = false) {
			return $batch;
		},
	],
	'computed' => [
		'batch' => function () {
			return $this->layout === 'table' ? false : $this->batch;
		}
	],
];
