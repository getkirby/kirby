<?php

namespace Kirby\Toolkit;

use JsonSerializable;
use Stringable;

/**
 * Marks a string as trusted, pre-escaped HTML so that it can flow through
 * Panel state to the frontend and be rendered as HTML without further
 * escaping. Any string not wrapped in this class should be treated as
 * untrusted on the frontend and escaped at the render site.
 *
 * On JSON serialization, parent keys of `HtmlString` values are renamed
 * from `key` to `<key>` so the frontend can rewrap them. Use
 * `HtmlString::resolve()` on a data array before encoding.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class HtmlString implements JsonSerializable, Stringable
{
	public function __construct(
		protected string $value
	) {
	}

	public function __toString(): string
	{
		return $this->value;
	}

	/**
	 * Checks whether a key has already been marked, so that
	 * `resolve()` can run more than once over the same data
	 * without turning `<key>` into `<<key>>`
	 */
	protected static function isMarked(int|string $key): bool
	{
		$key = (string)$key;

		return
			strlen($key) > 2 &&
			str_starts_with($key, '<') === true &&
			str_ends_with($key, '>') === true;
	}

	/**
	 * Checks whether the value is trusted HTML: an `HtmlString`
	 * or a list of them. A single plain entry disqualifies the
	 * whole list, so that untrusted values can never be marked
	 * as trusted. An empty array is not a list of trusted HTML,
	 * otherwise every empty array would get its key renamed.
	 */
	protected static function isTrusted(mixed $value): bool
	{
		if ($value instanceof HtmlString) {
			return true;
		}

		if (
			is_array($value) === false ||
			$value === [] ||
			array_is_list($value) === false
		) {
			return false;
		}

		return A::every($value, fn ($item) => $item instanceof HtmlString);
	}

	public function jsonSerialize(): string
	{
		return $this->value;
	}

	/**
	 * Walks an array recursively and renames any key whose
	 * value is an `HtmlString` (or a list of them) from
	 * `key` to `<key>`, so the JS side can detect and rewrap
	 * the value.
	 */
	public static function resolve(array $data): array
	{
		$result = [];

		foreach ($data as $key => $value) {
			if (static::isTrusted($value) === true) {
				// an already marked key is kept as it is
				if (static::isMarked($key) === false) {
					$key = '<' . $key . '>';
				}

				$result[$key] = $value;
				continue;
			}

			if (is_array($value) === true) {
				$result[$key] = static::resolve($value);
				continue;
			}

			$result[$key] = $value;
		}

		return $result;
	}

	public function value(): string
	{
		return $this->value;
	}
}
