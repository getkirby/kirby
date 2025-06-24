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
	 * Deletes all versions in the collection
	 */
	public function delete(): void
	{
		foreach ($this->data as $version) {
			$version->delete('*');
		}
	}

	/**
	 * Loads all available versions for a given model
	 *
	 * Versions need to be loaded in the order `changes`, `latest`
	 * to ensure that models are deleted correctly. The `latest`
	 * version always needs to be deleted last, otherwise the
	 * PlainTextStorage handler will not be able to clean up
	 * content directories.
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
