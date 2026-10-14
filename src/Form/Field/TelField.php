<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Tel Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TelField extends TextField
{
	#[Prop]
	protected string|null $autocomplete = 'tel';
	#[Prop]
	protected bool $counter = false;
	#[Prop]
	protected string|null $icon = 'phone';
}
