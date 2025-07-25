<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\FieldClass;
use Kirby\Form\Fields;
use Kirby\Form\Mixin\Converter;
use Kirby\Form\Mixin\Counter;
use Kirby\Form\Mixin\Font;
use Kirby\Form\Mixin\Maxlength;
use Kirby\Form\Mixin\Minlength;
use Kirby\Form\Mixin\Pattern;
use Kirby\Form\Mixin\Spellcheck;

/**
 * Main class file of the text field
 *
 * @package   Kirby Field
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextField extends FieldClass
{
	use Counter;
	use Converter;
	use Font;
	use Maxlength;
	use Minlength;
	use Pattern;
	use Spellcheck;

	public function __construct(
		string|null $after = null,
		bool $autofocus = false,
		string|null $before = null,
		mixed $converter = null,
		bool $counter = true,
		mixed $default = null,
		bool $disabled = false,
		string|null $font = null,
		string|null $help = null,
		string|null $icon = null,
		string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		string|null $pattern = null,
		array|string|null $placeholder = null,
		bool $required = false,
		Fields|null $siblings = null,
		bool $spellcheck = false,
		bool $translate = true,
		array|null $when = null,
		string|null $width = null,
		mixed $value = null
	) {
		// send parent props to the parent constructor
		parent::__construct(
			compact(
				'after', 'autofocus', 'before', 'default', 'disabled',
				'help', 'icon', 'label', 'model', 'name', 'placeholder',
				'required', 'siblings', 'translate', 'when', 'width', 'value'
			)
		);

		$this->setConverter($converter);
		$this->setCounter($counter);
		$this->setFont($font);
		$this->setMaxlength($maxlength);
		$this->setMinlength($minlength);
		$this->setPattern($pattern);
		$this->setSpellcheck($spellcheck);
	}

	public function default(): mixed
	{
		return $this->convert(parent::default());
	}

	public static function factory(array $props = []): static
	{
		return new static(...$props);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'converter'  => $this->converter(),
			'counter'    => $this->counter(),
			'font'       => $this->font(),
			'maxlength'  => $this->maxlength(),
			'minlength'  => $this->minlength(),
			'pattern'    => $this->pattern(),
			'spellcheck' => $this->spellcheck(),
		];
	}

	public function toFormValue(): mixed
	{
		return (string)$this->convert($this->value);
	}

	public function validations(): array
	{
		return [
			'minlength',
			'maxlength',
			'pattern'
		];
	}
}
