<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;

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
abstract class DisplayField extends FieldClass
{
	public function __construct(
		array|string|null $help = null,
		array|string|null $label = null,
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		$this->setHelp($help);
		$this->setLabel($label);
		$this->setName($name);
		$this->setWhen($when);
		$this->setWidth($width);
	}

	public function hasValue(): bool
	{
		return false;
	}

	public function props(): array
	{
		return [
			'help'     => $this->help(),
			'hidden'   => $this->isHidden(),
			'label'    => $this->label(),
			'name'     => $this->name(),
			'saveable' => $this->hasValue(),
			'type'     => $this->type(),
			'when'     => $this->when(),
			'width'    => $this->width(),
		];
	}
}
