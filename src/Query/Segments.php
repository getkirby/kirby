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
	/**
	 * Split query string into segments by dot
	 * but not inside (nested) parens
	 */
	public static function factory(string $query): static
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

		return new static($segments);
	}

	public function resolve(array|object $data = [])
	{
		$value = null;

		foreach ($this->data as $segment) {
			$value = $segment->resolve($value, $data);
		}

		return $value;
	}
}
