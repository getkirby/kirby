<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Shared surface for the source cursors `Line` and `Phrase`
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Cursor
{
	public function __construct(
		protected string $text = ''
	) {
	}

	/**
	 * Whether the current text contains the given
	 * needle (at least `$count` times).
	 */
	public function has(string $needle, int $count = 1): bool
	{
		return match ($count) {
			1       => str_contains($this->text(), $needle),
			default => substr_count($this->text(), $needle) >= $count
		};
	}

	/**
	 * Anchored match against the current text
	 */
	public function match(string $regex): array|null
	{
		return preg_match($regex, $this->text(), $matches) === 1 ? $matches : null;
	}

	public function text(): string
	{
		return $this->text;
	}
}
