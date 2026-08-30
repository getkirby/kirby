<?php

namespace Kirby\Form\Field;

/**
 * Tel Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TelField extends TextField
{
	public function autocomplete(): string
	{
		return $this->autocomplete ?? 'tel';
	}

	public function counter(): bool
	{
		return $this->counter ?? false;
	}

	public function icon(): string
	{
		return $this->icon ?? 'phone';
	}
}
