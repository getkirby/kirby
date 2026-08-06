<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\Model;

/**
 * Abilities for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileAbilities extends ModelAbilities
{
	/**
	 * @var File
	 */
	protected Model $model;

	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		parent::error(
			key: 'file.' . $key,
			data: [
				'id' => $this->model->id(),
				...$data
			],
			details: $details
		);
	}

	protected function ensureToChangeTemplate(): void
	{
		if (count($this->model->blueprints()) <= 1) {
			$this->error(key: 'changeTemplate');
		}
	}
}
