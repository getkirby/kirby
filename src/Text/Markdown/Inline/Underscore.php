<?php

namespace Kirby\Text\Markdown\Inline;

/**
 * Emphasis and strong emphasis via `_`
 *
 * @example
 * This wil be an _em_ tag.
 * This wil be a __strong__ tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Underscore extends Emphasis
{
	public static function markers(): array
	{
		return ['_'];
	}

	/**
	 * `_` may neither open nor close inside a word
	 */
	public function openClose(string $before, string $after): array
	{
		[$left, $right] = static::flanks($before, $after);

		return [
			$left  === true &&
			($right === false || static::punctuation($before) === true),
			$right === true &&
			($left  === false || static::punctuation($after)  === true)
		];
	}
}
