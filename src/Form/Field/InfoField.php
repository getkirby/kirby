<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldClass;

class InfoField extends FieldClass
{
	public function __construct(
		protected string $name,
		protected array|string|null $help = null,
		protected string|null $icon = null,
		protected array|string|null $label = null,

		/**
		 * Text to be displayed
		 */
		protected array|string|null $text = null,

		/**
		 * Change the design of the info box
		 */
		protected string|null $theme = null,
		protected array|null $when = null,
		protected string|null $width = null,
	) {
	}

	public function hasValue(): bool
	{
		return false;
	}

	public function props(): array
	{
		return [
			'help'     => $this->help(),
			'hidden'   => false,
			'icon'     => $this->icon(),
			'label'    => $this->label(),
			'saveable' => false,
			'text'     => $this->text(),
			'theme'    => $this->theme(),
			'type'     => $this->type(),
			'when'     => $this->when(),
			'width'    => $this->width(),
		];
	}

	public function text(): string|null
	{
		if ($this->text === null) {
			return null;
		}

		$text = $this->i18n($this->text, $this->text);
		$text = $this->model()->toSafeString($text);
		$text = $this->kirby()->kirbytext($text);

		return $text;
	}

	public function type(): string
	{
		return 'info';
	}
}
