<?php

namespace Kirby\Form\FieldAbstract;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Fields;
use Kirby\Form\Mixin;

/**
 * Abstract field class to be used for fields with
 * value (e.g. text, number, checkbox, etc.)
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ValueField extends DisplayField
{
	use Mixin\Autofocus;
	use Mixin\Placeholder;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;

	public function __construct(
		bool $autofocus = false,
		mixed $default = null,
		bool $disabled = false,
		array|string|null $label = null,
		array|string|null $help = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool $required = false,
		Fields|null $siblings = null,
		bool $translate = true,
		mixed $value = null,
		array|null $when = null,
		string|null $width = null,
	) {
		parent::__construct(
			disabled: $disabled,
			help: $help,
			label: $label,
			model: $model,
			name: $name,
			siblings: $siblings,
			when: $when,
			width: $width
		);

		$this->setAutofocus($autofocus);
		$this->setDefault($default);
		$this->setPlaceholder($placeholder);
		$this->setRequired($required);
		$this->setTranslate($translate);

		$this->fill($value);
	}

	public function hasValue(): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'autofocus'   => $this->autofocus(),
			'default'     => $this->default(),
			'placeholder' => $this->placeholder(),
			'required'    => $this->isRequired(),
			'translate'   => $this->translate(),
		];
	}
}
