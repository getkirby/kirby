<?php

namespace Kirby\Query;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;

/**
 * The Segments class helps splitting a
 * query string into processable segments
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Segments extends Collection
{
	public function __construct(
		array $data = [],
		protected Query|null $parent = null,
	) {
		parent::__construct($data);
	}

	/**
	 * Split query string into segments by dot
	 * but not inside (nested) parens
	 */
	public static function factory(string $query, Query $parent = null): static
	{
		$segments = static::parse($query);
		$index    = 0;

		$segments = A::map(
			$segments,
			function ($segment) use (&$index) {
				if (in_array($segment, ['.', '.?']) === true) {
					return $segment;
				}

				$index++;
				return Segment::factory($segment, $index - 1);
			}
		);

		return new static($segments, $parent);
	}

	/**
	 * Splits the string of a segment chaing into an
	 * array of segments as well as conenctors (`.` or `.?`)
	 * @internal
	 */
	public static function parse(string $string): array
	{
		return preg_split(
			'/(\.\??)|(\(([^()]+|(?2))*+\))(*SKIP)(*FAIL)/',
			trim($string),
			flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
		);
	}

	/**
	 * Resolves the segments chain by looping through
	 * each segment call to be applied to the value of
	 * all previous segment calls, returning gracefully at
	 * `.?` when current value is `null`
	 */
	public function resolve(array|object $data = [])
	{
		$value = null;

		foreach ($this->data as $segment) {
			// optional chaining: stop if current value is null
			if ($segment === '.?' && $value === null) {
				return null;
			}

			// for regular connectors, just skip
			if ($segment === '.') {
				continue;
			}

			// offer possibility to intercept on objects
			if ($value !== null) {
				$value = $this->parent?->intercept($value) ?? $value;
			}

			$value = $segment->resolve($value, $data);
		}

		return $value;
	}
}
