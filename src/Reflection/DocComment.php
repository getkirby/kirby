<?php

namespace Kirby\Reflection;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

class DocComment
{
	public function __construct(
		protected string $comment,
	) {
	}

	public static function from(
		ReflectionClass|ReflectionFunction|ReflectionMethod|ReflectionProperty|null $object
	) {
		return new static($object?->getDocComment() ?? '');
	}

	/**
	 * Returns the cleaned docblock text of the given property.
	 */
	public function description(): string|null
	{
		$comment = preg_replace(['#^/\*\*#', '#\*/$#'], '', $this->comment);
		$lines   = preg_split('/\R/', (string)$comment) ?: [];
		$lines   = array_map(static fn (string $line): string => ltrim(trim($line), "* \t"), $lines);
		$lines   = array_filter(
			$lines,
			static fn (string $line): bool => $line !== '' && str_starts_with($line, '@') === false
		);

		return implode(' ', $lines);
	}
}
