<?php

namespace Kirby\Content;

use Kirby\Cms\Collection;
use Kirby\Cms\ModelWithContent;

/**
 * @package   Kirby Content
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Collection<\Kirby\Content\Version>
 */
class Versions extends Collection
{
	/**
	 * Loads all available versions for a given model
	 */
	public static function load(
		ModelWithContent $model
	): static {
		return new static(
			objects: [
				$model->version('changes'),
				$model->version('latest'),
			],
			parent: $model
		);
	}
}