<?php

namespace Kirby\Form\Mixin;

/**
 * Width functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Width
{
	/**
	 * The width of the field in the field grid.
	 * Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
	 */
	protected string|null $width;

	protected function setWidth(string|null $width = null): void
	{
		$this->width = $width;
	}

	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
