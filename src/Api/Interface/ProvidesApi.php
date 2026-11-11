<?php

namespace Kirby\Api\Interface;

/**
 * Implemented by objects with their own API routes
 * (e.g. form fields), which `Api::forward()` can call
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     7.0.0
 */
interface ProvidesApi
{
	/**
	 * Returns the route definitions
	 */
	public function api(): array;
}
