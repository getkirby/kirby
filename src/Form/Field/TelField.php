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
	protected string|null $autocomplete = 'tel';
	protected bool $counter = false;
	protected string|null $icon = 'phone';
}
