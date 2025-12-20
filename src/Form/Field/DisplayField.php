<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Base class for fields that have no value
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class DisplayField extends BaseField
{
	use Mixin\Help;
	use Mixin\Label;
	use Mixin\Width;

	public function __construct(
		array|string|null $help = null,
		array|string|null $label = null,
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			name: $name,
			when: $when
		);

		$this->help  = $help;
		$this->label = $label;
		$this->width = $width;
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
