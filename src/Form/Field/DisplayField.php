<?php

namespace Kirby\Form\Field;

use Kirby\Form\Field;
use Kirby\Form\Mixin;

/**
 * Base class for fields that have no value
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DisplayField extends Field
{
	use Mixin\Help;
	use Mixin\Label;
	use Mixin\Width;

	public function __construct(
		array|string|null $help = null,
		array|string|null $label = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->help  = $help;
		$this->label = $label;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'help'  => $this->help(),
			'label' => $this->label(),
			'width' => $this->width(),
		];
	}
}
