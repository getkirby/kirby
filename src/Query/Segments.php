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
final class Segments extends Collection
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
		$segments = preg_split(
			'!\.|(\(([^()]+|(?1))*+\))(*SKIP)(*FAIL)!',
			trim($query),
			-1,
			PREG_SPLIT_NO_EMPTY
		);

		$segments = A::map(
			array_keys($segments),
			fn ($index) => Segment::factory($segments[$index], $index)
		);

		return new static($segments, $parent);
	}

	public function resolve(array|object $data = [])
	{
		$value = null;

		foreach ($this->data as $segment) {
			// offer possibility to intercept on objects
			if ($value !== null) {
				$value = $this->parent?->intercept($value) ?? $value;
			}

			$value = $segment->resolve($value, $data);
		}

		return $value;
	}
}
