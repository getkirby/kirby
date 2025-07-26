<?php

namespace Kirby\Form\Mixin;

/**
 * Font functionality for fields
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
trait Font
{
	/**
	 * The font used for the field.
	 */
	protected string $font;

	public function font(): string
	{
		return $this->font;
	}

	protected function setFont(string|null $font = null): void
	{
		$this->font = $font === 'monospace' ? 'monospace' : 'sans-serif';
	}
}
