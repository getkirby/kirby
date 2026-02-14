<?php

namespace Kirby\Form\Field;

/**
 * Headline field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class HeadlineField extends DisplayField
{
	public function __construct(
		array|string|null $label = null,
		array|string|null $help = null,
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			label: $label,
			help:  $help,
			name:  $name,
			when:  $when,
			width: $width
		);
	}
}
