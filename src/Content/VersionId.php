<?php

namespace Kirby\Content;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Stringable;

/**
 * The Version ID identifies a version of content.
 * This can be the currently latest version or changes
 * to the content. In the future, we also plan to use this
 * for older revisions of the content.
 *
 * @internal
 * @since 5.0.0
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class VersionId implements Stringable
{
	/**
	 * Latest stable version of the content
	 */
	public const LATEST = 'latest';

	/**
	 * Latest changes to the content (optional)
	 */
	public const CHANGES = 'changes';

	/**
	 * A global store for a version id that should be
	 * rendered for each model in a live preview scenario.
	 */
	public static self|null $render = null;

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException If the version ID is not valid
	 */
	public function __construct(
		public string $value
	) {
		if (in_array($value, [static::CHANGES, static::LATEST], true) === false) {
			throw new InvalidArgumentException(message: 'Invalid Version ID');
		}
	}

	/**
	 * Converts the VersionId instance to a simple string value
	 */
	public function __toString(): string
	{
		return $this->value;
	}

	/**
	 * List of available version ids
	 */
	public static function all(): array
	{
		return [
			static::latest(),
			static::changes(),
		];
	}

	/**
	 * Creates a VersionId instance for the latest content changes
	 */
	public static function changes(): static
	{
		return new static(static::CHANGES);
	}

	/**
	 * Creates a VersionId instance from a simple string value
	 */
	public static function from(VersionId|string $value): static
	{
		if ($value instanceof VersionId) {
			return $value;
		}

		return new static($value);
	}

	/**
	 * Compares a string value with the id value
	 */
	public function is(string $value): bool
	{
		return $value === $this->value;
	}

	/**
	 * Creates a VersionId instance for the latest stable version of the content
	 */
	public static function latest(): static
	{
		return new static(static::LATEST);
	}

	/**
	 * Temporarily sets the version ID for preview rendering
	 * only for the logic in the callback
	 */
	public static function render(VersionId|string $versionId, Closure $callback): mixed
	{
		$original       = static::$render;
		static::$render = static::from($versionId);

		try {
			return $callback();
		} finally {
			// ensure that the render version ID is *always* reset
			// to the original value, even if an error occurred
			static::$render = $original;
		}
	}

	/**
	 * Returns the ID value
	 */
	public function value(): string
	{
		return $this->value;
	}
}
