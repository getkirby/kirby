<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\Collection as BaseCollection;

/**
 * NestCollection
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class NestCollection extends BaseCollection
{
	/**
	 * Converts all objects in the collection
	 * to an array. This can also take a callback
	 * function to further modify the array result.
	 *
	 * @param \Closure|null $map
	 * @return array
	 */
	public function toArray(Closure $map = null): array
	{
		return parent::toArray($map ?? fn ($object) => $object->toArray());
	}
}
