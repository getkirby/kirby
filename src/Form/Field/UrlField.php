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
	protected array|string|null $placeholder = 'https://example.com';
	protected string|null $autocomplete = 'url';
	protected bool $counter = false;
	protected string|null $icon = 'url';

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'url'
		];
	}
}
