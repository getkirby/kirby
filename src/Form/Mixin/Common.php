<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

trait Common
{
	protected string|array|null $after = null;
	protected bool $autofocus = false;
	protected string|array|null $before = null;
	protected bool $disabled = false;
	protected string|array|null $help = null;
	protected string|null $icon = null;
	protected string|array|null $label = null;
	protected string|null $name = null;
	protected string|array|null $placeholder = null;
	protected bool $required = false;
	protected string|null $width = null;

	/**
	 * Optional text that will be shown after the input
	 */
	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets focused
	 */
	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	/**
	 * Optional text that will be shown before the input
	 */
	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
	}

	/**
	 * @deprecated 5.0.0 Use `::isDisabled` instead
	 */
	public function disabled(): bool
	{
		return $this->isDisabled();
	}

	/**
	 * Optional help text below the field
	 */
	public function help(): string|null
	{
		if (empty($this->help) === false) {
			return $this->kirby()->kirbytext(
				$this->stringTemplate($this->help, safe: true)
			);
		}

		return null;
	}

	/**
	 * Translate field parameters
	 */
	protected function i18n(string|array|null $param = null): string|null
	{
		return empty($param) === false ? I18n::translate($param, $param) : null;
	}

	/**
	 * Optional icon that will be shown at the end of the field
	 */
	public function icon(): string|null
	{
		return $this->icon;
	}

	/**
	 * The field id is used in fields collections. The name is used as id by default.
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	/**
	 * Checks if the field is hidden
	 */
	public function isHidden(): bool
	{
		return false;
	}

	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	public function isRequired(): bool
	{
		return $this->required;
	}

	/**
	 * Checks if the field is saveable
	 */
	public function isSaveable(): bool
	{
		return true;
	}

	/**
	 * The field label can be set as string or associative array with translations
	 */
	public function label(): string|null
	{
		return $this->stringTemplate(
			$this->label ?? Str::ucfirst($this->name())
		);
	}

	/**
	 * Returns the field name
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
	}

	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	public function placeholder(): string|null
	{
		return $this->stringTemplate($this->placeholder);
	}

	/**
	 * @deprecated 5.0.0 Use `::isRequired` instead
	 */
	public function required(): bool
	{
		return $this->isRequired();
	}

	/**
	 * Checks if the field is saveable
	 * @deprecated 5.0.0 Use `::isSaveable()` instead
	 */
	public function save(): bool
	{
		return $this->isSaveable();
	}

	/**
	 * The width of the field in the field grid. Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
	 */
	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
