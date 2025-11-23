<?php

namespace Kirby\Toolkit;

use Closure;

/**
 * Adds a i18n helper method to the class
 *
 * @package   Kirby Toolkit
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait HasI18n
{
	/**
	 * Translates a key or template string
	 */
	protected function i18n(
		Closure|string|array|null $key,
		array|null $data = null
	): string|null {

		if ($key instanceof Closure) {
			$key = $key();
		}

		if ($key === null) {
			return null;
		}

		if ($data === null) {
			return I18n::translate($key, $key);
		}

		return I18n::template($key, $key, $data);
	}
}
