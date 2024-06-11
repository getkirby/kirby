<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Stringable;

/**
 * The Version ID identifies a version of content.
 * This can be the currently published version or changes
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
	public const PUBLISHED = 'published';

	/**
	 * Latest changes to the content (optional)
	 */
	public const CHANGES   = 'changes';

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException If the version ID is not valid
	 */
	public function __construct(
		public string $value
	) {
		if (in_array($value, [static::CHANGES, static::PUBLISHED]) === false) {
			throw new InvalidArgumentException('Invalid Version ID');
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
	 * Creates a VersionId instance for the latest content changes
	 */
	public static function changes(): static
	{
		return new static(static::CHANGES);
	}

	/**
	 * Returns the default version id for the model
	 */
	public static function default(ModelWithContent $model): static
	{
		if (
			$model instanceof Page === true &&
			$model->isDraft() === true
		) {
			return VersionId::changes();
		}

		return VersionId::published();
	}

	/**
	 * Creates a VersionId instance from a simple string value
	 */
	public static function from(self|string $value): static
	{
		if ($value instanceof static) {
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
	public static function published(): static
	{
		return new static(static::PUBLISHED);
	}

	/**
	 * Returns the ID value
	 */
	public function value(): string
	{
		return $this->value;
	}
}
