<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Files;
use Kirby\Toolkit\Escape;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FilesSearchController extends ModelsSearchController
{
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

	/**
	 * @param \Kirby\Cms\File $model
	 */
	public function result($model): array
	{
		return [
			'image' => $model->panel()->image(),
			'text'  => Escape::html($model->filename()),
			'link'  => $model->panel()->url(true),
			'info'  => Escape::html($model->id()),
			'uuid'  => $model->uuid()->toString(),
		];
	}
}
