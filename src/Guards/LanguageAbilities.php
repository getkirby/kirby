<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;

/**
 * Abilities for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelAbilities<Language>
 */
class LanguageAbilities extends ModelAbilities
{
	protected function error(
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
		if ($this->model->isDefault() === true && $this->model->isLastLanguage() === false) {
			$this->error(key: 'delete.default');
		}
	}
}
