<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldOptions;
use Kirby\Form\Mixin;

/**
 * Select Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SelectField extends OptionField
{
	use Mixin\Icon;
	use Mixin\Placeholder;

	public function __construct(
		string|null $icon = null,
		array|string|null $placeholder = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->icon        = $icon;
		$this->placeholder = $placeholder;
	}

	protected function fetchOptions(): array
	{
		$props = FieldOptions::polyfill(['options' => $this->options ?? []]);

		// disable safe mode as the select field does not
		// render HTML for the option text
		$options = FieldOptions::factory($props['options'], false);

		return $options->render($this->model());
	}

	public function placeholder(): string|null
	{
		return $this->stringTemplateI18n($this->placeholder) ?? '—';
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'icon'        => $this->icon(),
			'placeholder' => $this->placeholder(),
		];
	}
}
