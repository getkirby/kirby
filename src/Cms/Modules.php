<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

/**
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Modules extends Collection
{

	public static function collect(
		Module|Page|Site $parent,
		string $group,
		string|null $type = null
	): static {
		$modules  = new static;
		$root     = $parent->root() . '/_modules/' . $group;
		$children = Dir::inventory($root)['children'];

		foreach ($children as $child) {
			$module = new Module(
				group: $group,
				num: $child['num'],
				parent: $parent,
				slug: $child['slug'],
				type: $type
			);

			$modules->add($module);
		}

		return $modules;
	}

}
