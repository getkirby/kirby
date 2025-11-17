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
trait Label
{
	/**
	 * The field label can be set as string or associative array with translations
	 */
	protected array|string|null $label;

	public function label(): string|null
	{
		$label = $this->label ?? Str::ucfirst($this->name());
		$label = $this->i18n($label);

		return $this->stringTemplate($label);
	}

	protected function setLabel(array|string|null $label = null): void
	{
		$this->label = $label;
	}
}
