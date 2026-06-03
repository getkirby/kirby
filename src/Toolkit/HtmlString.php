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

	public function jsonSerialize(): string
	{
		return $this->value;
	}

	/**
	 * Walks an array recursively and renames any key
	 * whose value is an `HtmlString` from `key` to `<key>`,
	 * so the JS side can detect and rewrap the value.
	 */
	public static function resolve(array $data): array
	{
		$result = [];

		foreach ($data as $key => $value) {
			if ($value instanceof HtmlString) {
				$result['<' . $key . '>'] = $value;
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
