<?php

namespace Kirby\Form\Field;

/**
 * Url Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UrlField extends TextField
{
	public function autocomplete(): string
	{
		return $this->autocomplete ?? 'url';
	}

	public function counter(): bool
	{
		return $this->counter ?? false;
	}

	public function icon(): string
	{
		return $this->icon ?? 'url';
	}

	public function placeholder(): string
	{
		return parent::placeholder() ?? 'https://example.com';
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'url'
		];
	}
}
