<?php

return [
	'props' => [
		/**
		 * Enables/disables reverse sorting
		 */
		'flip' => function (bool $flip = false) {
			return $flip;
		},
		/**
		 * Enables/disables manual sorting
		 */
		'sortable' => function (bool $sortable = true) {
			return $sortable;
		},
		/**
		 * Overwrites manual sorting and sorts by the given field and sorting direction (i.e. `date desc`)
		 */
		'sortBy' => function (string|null $sortBy = null) {
			return $sortBy;
		},
	],
	'computed' => [
		'sortable' => function () {
			if ($this->sortable === false) {
				return false;
			}

			if (
				$this->type === 'pages' &&
				in_array($this->status, ['listed', 'published', 'all'], true) === false
			) {
				return false;
			}

			// don't allow sorting while search filter is active
			if (empty($this->searchterm()) === false) {
				return false;
			}

			if ($this->query !== null) {
				return false;
			}

			if ($this->sortBy !== null) {
				return false;
			}

			if ($this->flip === true) {
				return false;
			}

			return true;
		}
	]
];
