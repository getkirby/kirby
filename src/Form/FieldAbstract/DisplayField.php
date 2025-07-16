<?php

namespace Kirby\Form\FieldAbstract;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Fields;
use Kirby\Form\Mixin;

/**
 * Abstract field class to be used for fields without
 * value (e.g. info or stats fields)
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class DisplayField extends BaseField
{
	use Mixin\Help;
	use Mixin\Label;

	public function __construct(
		bool $disabled = false,
		array|string|null $label = null,
		array|string|null $help = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		Fields|null $siblings = null,
		array|null $when = null,
		string|null $width = null,
	) {
		parent::__construct(
			disabled: $disabled,
			model: $model,
			name: $name,
			siblings: $siblings,
			when: $when,
			width: $width
		);

		$this->setHelp($help);
		$this->setLabel($label);
	}

	public function hasValue(): bool
	{
		return false;
	}

	public function isHidden(): bool
	{
		return false;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'help'  => $this->help(),
			'label' => $this->label(),
		];
	}
}
