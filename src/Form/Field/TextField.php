<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Text Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextField extends StringField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Converter;
	use Mixin\Icon;
	use Mixin\Pattern;

	public function __construct(
		array|string|null $after = null,
		array|string|null $before = null,
		string|null $icon = null,
		string|null $pattern = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->after   = $after;
		$this->before  = $before;
		$this->icon    = $icon;
		$this->pattern = $pattern;
	}

	public function default(): string
	{
		return $this->convert(value: parent::default()) ?? '';
	}

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: $this->convert($value) ?? ''
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'after'     => $this->after(),
			'before'    => $this->before(),
			'converter' => $this->converter(),
			'icon'      => $this->icon(),
			'pattern'   => $this->pattern()
		];
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'pattern'
		];
	}
}
