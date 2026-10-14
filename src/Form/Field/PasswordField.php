<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Password field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PasswordField extends TextField
{
	#[Prop]
	protected string|null $icon = 'key';
}
