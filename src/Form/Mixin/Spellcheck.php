<?php

namespace Kirby\Form\Mixin;

/**
 * Spellcheck functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 * @since 6.0.0
 *
 * @package   Kirby Form
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Spellcheck
{
	/**
	 * Enable or disable spellcheck for the field
	 */
	protected bool $spellcheck;

	public function spellcheck(): bool
	{
		return $this->spellcheck;
	}

	protected function setSpellcheck(bool $spellcheck = false): void
	{
		$this->spellcheck = $spellcheck;
	}
}
