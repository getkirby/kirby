<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\Str;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Decorators
{
	protected string|array|null $after = null;
	protected string|array|null $before = null;
	protected string|array|null $help = null;
	protected string|null $icon = null;
	protected string|array|null $label = null;
	protected string|array|null $placeholder = null;

	/**
	 * Optional text that will be shown after the input
	 */
	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	/**
	 * Optional text that will be shown before the input
	 */
	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
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
	 * Optional icon that will be shown at the end of the field
	 */
	public function icon(): string|null
	{
		return $this->icon;
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
	 * Optional placeholder value that will be shown when the field is empty
	 */
	public function placeholder(): string|null
	{
		return $this->stringTemplate($this->placeholder);
	}

	protected function setAfter(array|string|null $after = null): void
	{
		$this->after = $this->i18n($after);
	}

	protected function setBefore(array|string|null $before = null): void
	{
		$this->before = $this->i18n($before);
	}

	protected function setHelp(array|string|null $help = null): void
	{
		$this->help = $this->i18n($help);
	}

	protected function setIcon(string|null $icon = null): void
	{
		$this->icon = $icon;
	}

	protected function setLabel(array|string|null $label = null): void
	{
		$this->label = $this->i18n($label);
	}

	protected function setPlaceholder(array|string|null $placeholder = null): void
	{
		$this->placeholder = $this->i18n($placeholder);
	}
}
