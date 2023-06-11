<?php

namespace Kirby\Content;

use Kirby\Exception\InvalidArgumentException;

/**
 * Identifies a single version like the published
 * version, the changes version or a specific revision
 * (via the `RevisionVersionIdentifier` child class)
 * @internal
 * @since 4.0.0
 *
 * @package   Kirby Content
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class VersionIdentifier
{
	/**
	 * @param 'published'|'changes' $type
	 * @throws \Kirby\Exception\InvalidArgumentException For an invalid type argument
	 */
	public function __construct(
		protected string $type
	) {
		if (in_array($type, ['published', 'changes']) !== true) {
			throw new InvalidArgumentException('Invalid version type "' . $type . '"');
		}
	}

	/**
	 * Returns an ID string that identifies the version;
	 * to be extended with type-specific data in child classes
	 */
	public function __toString(): string
	{
		return $this->type;
	}

	/**
	 * Shorthand for a `changes` instance
	 */
	public static function changes(): self
	{
		return new self('changes');
	}

	/**
	 * Shorthand for a `published` instance
	 */
	public static function published(): self
	{
		return new self('published');
	}

	/**
	 * Returns the version type
	 *
	 * @return 'published'|'changes'
	 */
	public function type(): string
	{
		return $this->type;
	}
}
