<?php

namespace Kirby\Form\Mixin;

/**
 * Pattern functionality for fields
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
trait Pattern
{
	/**
	 * The pattern to validate the field value against.
	 */
	protected string|null $pattern;

	public function pattern(): string|null
	{
		return $this->pattern;
	}

	protected function setPattern(string|null $pattern = null): void
	{
		$this->pattern = $pattern;
	}
}
