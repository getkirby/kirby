<?php

namespace Kirby\Form\Field;

/**
 * Password field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PasswordField extends TextField
{
	public function icon(): string
	{
		return $this->icon ?? 'key';
	}
}
