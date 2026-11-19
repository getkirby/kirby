<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Url Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UrlField extends TextField
{
	#[Prop]
	protected string|null $autocomplete = 'url';

	#[Prop]
	protected bool $counter = false;

	#[Prop]
	protected string|null $icon = 'url';

	#[Prop]
	protected array|string|null $placeholder = 'https://example.com';

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'url'
		];
	}
}
