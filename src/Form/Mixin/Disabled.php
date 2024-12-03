<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\Language;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Disabled
{
	protected bool $disabled = false;

	/**
	 * Returns the custom disabled state
	 */
	public function disabled(): bool
	{
		return $this->disabled ?? false;
	}

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function isDisabled(Language|string $language = 'current'): bool
	{
		if ($this->isTranslatableInto($language) === false) {
			return true;
		}

		return $this->disabled();
	}

	/**
	 * Setter for the disabled state
	 */
	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}
}
