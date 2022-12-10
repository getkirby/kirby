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
		$position = 0;

		$segments = A::map(
			$segments,
			function ($segment) use (&$position) {
				// leave connectors as they are
				if (in_array($segment, ['.', '?.']) === true) {
					return $segment;
				}

				// turn all other parts into Segment objects
				// and pass their position in the chain (ignoring connectors)
				$position++;
				return Segment::factory($segment, $position - 1);
			}
		);

		return new static($segments, $parent);
	}

	/**
	 * Splits the string of a segment chaing into an
	 * array of segments as well as conenctors (`.` or `?.`)
	 * @internal
	 */
	public static function parse(string $string): array
	{
		return preg_split(
			'/(\??\.)|(\(([^()]+|(?2))*+\))(*SKIP)(*FAIL)/',
			trim($string),
			flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
		);
	}

	/**
	 * Resolves the segments chain by looping through
	 * each segment call to be applied to the value of
	 * all previous segment calls, returning gracefully at
	 * `?.` when current value is `null`
	 */
	public function resolve(array|object $data = [])
	{
		$value = null;

		foreach ($this->data as $segment) {
			// optional chaining: stop if current value is null
			if ($segment === '?.' && $value === null) {
				return null;
			}

			// for regular connectors and optional chaining on non-null,
			// just skip this connecting segment
			if ($segment === '.' || $segment === '?.') {
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
