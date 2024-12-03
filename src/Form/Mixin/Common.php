<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Common
{
	protected bool $autofocus = false;
	protected string|null $name = null;
	protected string|null $width = null;

	/**
	 * Sets the focus on this field when the form loads. Only the first field with this label gets focused
	 */
	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	/**
	 * Helper to translate field parameters
	 */
	protected function i18n(string|array|null $param = null): string|null
	{
		return I18n::translate($param, $param);
	}

	/**
	 * The field id is used in fields collections. The name is used as id by default.
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * Checks if the field is hidden
	 */
	public function isHidden(): bool
	{
		return false;
	}

	/**
	 * Returns the field name and falls back to the type if no name is given
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
	}

	/**
	 * Setter for the autofocus state
	 */
	protected function setAutofocus(bool $autofocus = false): void
	{
		$this->autofocus = $autofocus;
	}
	
	/**
	 * Setter for the name property
	 */
	protected function setName(string|null $name = null): void
	{
		$this->name = $name;
	}

	/**
	 * Setter for the field width. See `::width()` for available widths
	 */
	protected function setWidth(string|null $width = null): void
	{
		$this->width = $width;
	}

	/**
	 * The width of the field in the field grid. Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
	 */
	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
