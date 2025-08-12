<?php

namespace Kirby\Form\Mixin;

/**
 * API functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Api
{
	public function api(): array
	{
		return $this->routes();
	}

	/**
	 * Routes for the field API
	 */
	public function routes(): array
	{
		return [];
	}
}
