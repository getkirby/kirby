<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Files;
use Kirby\Panel\Ui\Item\FileItem;
use Override;

/**
 * Controls the search requests for files
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FilesSearchController extends ModelsSearchController
{
	/**
	 * @param \Kirby\Cms\File $model
	 */
	#[Override]
	public function item($model): FileItem
	{
		return new FileItem(file: $model, info: '{{ file.id }}');
	}

	#[Override]
	public function models(): Files
	{
		$files = $this->kirby->site()
			->index(true)
			->filter('isListable', true)
			->files();

		// add site files which aren't considered by the index
		$files = $files->add($this->kirby->site()->files());

		// filter and search among those files
		$files = $files->filter('isListable', true)->search($this->query);

		return $files;
	}
}
