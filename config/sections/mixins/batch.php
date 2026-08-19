<?php

use Kirby\Exception\Exception;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\I18n;

return [
	'props' => [
		/**
		 * Activates the batch delete option for the section
		 */
		'batch' => function (bool $batch = false) {
			return $batch;
		},
	],
	'methods' => [
		'deleteSelected' => function (array $ids): bool {
			if ($ids === []) {
				return true;
			}

			// check if batch deletion is allowed
			if ($this->batch() === false) {
				throw new PermissionException(
					message: 'The section does not support batch actions'
				);
			}

			$min = $this->min();

			// check if the section has enough items after the deletion
			if ($this->total() - count($ids) < $min) {
				$type = match ($this->type()) {
					'files' => 'filelist',
					'pages' => 'pagelist',
					default => $this->type()
				};

				throw new Exception(
					message: I18n::template('error.field.' . $type . '.min.' . I18n::form($min), [
						'min'   => $min,
						'field' => $this->headline()
					])
				);
			}

			$this->models()->delete($ids);
			return true;
		}
	]
];
