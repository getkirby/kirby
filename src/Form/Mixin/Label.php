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
	protected array|string|null $label = null;

	public function label(): string|null
	{
		if ($this->label === null || $this->label === []) {
			return Str::ucfirst($this->name());
		}

		return $this->stringTemplate($this->i18n($this->label));
	}

	protected function setLabel(array|string|null $label): void
	{
		$this->label = $label;
	}
}
