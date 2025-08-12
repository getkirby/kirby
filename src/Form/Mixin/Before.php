<?php

namespace Kirby\Form\Mixin;

/**
 * Before field functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Before
{
	/**
	 * Optional text that will be shown before the input
	 */
	protected string|null $before;

	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
	}

	protected function setBefore(array|string|null $before = null): void
	{
		$this->before = $this->i18n($before);
	}
}
