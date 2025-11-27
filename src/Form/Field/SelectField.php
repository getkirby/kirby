<?php

namespace Kirby\Form\Field;

use Kirby\Field\FieldOptions;
use Kirby\Form\Mixin;

/**
 * Select Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SelectField extends OptionField
{
	use Mixin\Icon;
	use Mixin\Placeholder;

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		string|null $name = null,
		array|string|null $options = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default:   $default,
			disabled:  $disabled,
			help:      $help,
			label:     $label,
			name:      $name,
			options:   $options,
			required:  $required,
			translate: $translate,
			when:      $when,
			width:     $width
		);

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
