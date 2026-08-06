<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\Model;

/**
 * Abilities for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LanguageAbilities extends ModelAbilities
{
	/**
	 * @var Language
	 */
	protected Model $model;

	public function error(
		string $key,
		array $data = [],
		array $details = []
	): never {
		parent::error(
			key: 'language.' . $key,
			data: [
				'code' => $this->model->code(),
				...$data
			],
			details: $details
		);
	}

	protected function ensureToDelete(): void
	{
		// a single-language object cannot be deleted
		if ($this->model->isSingle() === true) {
			$this->error(key: 'delete.single');
		}

		// the default language can only be deleted if it's the last
		if ($this->model->isDefault() === true && $this->model->isLast() === false) {
			$this->error(key: 'delete.default');
		}
	}
}
