<?php

namespace Kirby\Toolkit;

use Closure;

/**
 * Adds a i18n helper method to the class
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait HasI18n
{
	/**
	 * Translates a key or template string
	 * @return ($key is string|array ? string : string|HtmlString|null)
	 */
	protected static function i18n(
		Closure|string|array|HtmlString|null $key,
		array|null $data = null
	): string|HtmlString|null {
		if ($key instanceof Closure) {
			$key = $key();
		}

		if ($key === null) {
			return null;
		}

		if ($key instanceof HtmlString) {
			return $key;
		}

		if ($data === null) {
			return I18n::translate($key, $key);
		}

		return I18n::template($key, $key, $data);
	}

	/**
	 * Translates a key or template string and marks the result as
	 * trusted HTML, escaping every filled-in placeholder.
	 *
	 * @since 6.0.0
	 */
	protected static function i18nHtml(
		Closure|string|array|HtmlString|null $key,
		array $data = []
	): HtmlString|null {
		if ($key instanceof Closure) {
			$key = $key();
		}

		if ($key === null) {
			return null;
		}

		if ($key instanceof HtmlString) {
			return $key;
		}

		return HtmlString::translate($key, $data);
	}
}
