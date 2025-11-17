<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;
use Kirby\Form\Mixin;
use Kirby\Reflection\Constructor;
use Kirby\Toolkit\Str;

class TextField extends FieldClass
{
	use Mixin\Autocomplete;
	use Mixin\Counter;
	use Mixin\Font;
	use Mixin\Maxlength;
	use Mixin\Minlength;
	use Mixin\Pattern;
	use Mixin\Spellcheck;

	/**
	 * @param $converter The field value will be converted with the selected converter before the value gets saved. Available converters: `lower`, `upper`, `ucfirst`, `slug`
	 */
	public function __construct(
		protected string $name,
		protected array|string|null $after = null,
		protected string|null $autocomplete = null,
		protected bool $autofocus = false,
		protected array|string|null $before = null,
		protected string|null $converter = null,
		protected bool $counter = true,
		protected mixed $default = null,
		protected bool $disabled = false,
		protected string|null $font = null,
		protected array|string|null $help = null,
		protected string|null $icon = null,
		protected array|string|null $label = null,
		protected int|null $maxlength = null,
		protected int|null $minlength = null,
		protected array|string|null $placeholder = null,
		protected bool $spellcheck = false,
		protected string|null $pattern = null,
		protected bool $required = false,
		protected bool $translate = true,
		protected array|null $when = null,
		protected string|null $width = null,
		protected mixed $value = ''
	) {
		$this->fill($value);
	}

	public function convert($value)
	{
		if ($converterName = $this->converter()) {
			return $this->converters()[$converterName]($value);
		}

		return $value;
	}

	public function converter(): string|null
	{
		if (isset($this->converter) === true && $this->converter !== null && array_key_exists($this->converter, $this->converters()) === true) {
			return $this->converter;
		}

		return null;
	}

	public function converters(): array
	{
		return [
			'lower'   => Str::lower(...),
			'slug'    => Str::slug(...),
			'ucfirst' => Str::ucfirst(...),
			'upper'   => Str::upper(...),
		];
	}

	public function default(): mixed
	{
		return $this->convert($this->default);
	}

	public function fill(mixed $value): static
	{
		$this->value = (string)$this->convert($this->value);
		return $this;
	}

	public function props(): array
	{
		$constructor = new Constructor($this);
		$props       = [];
		$params      = $constructor->getParameterNames();
		$ignore      = ['value'];

		foreach ($params as $param) {
			if (in_array($param, $ignore) === true) {
				continue;
			}

			$props[$param] = $this->$param();
		}

		$props['hidden']   = $this->isHidden();
		$props['saveable'] = $this->hasValue();
		$props['type']     = $this->type();

		ksort($props);

		return array_filter($props, fn($value) => $value !== null);
	}

	public function type(): string
	{
		return 'text';
	}

	protected function validations(): array
	{
		return [
			'minlength',
			'maxlength',
			'pattern'
		];
	}
}
