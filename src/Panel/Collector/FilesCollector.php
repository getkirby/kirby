<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Users;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends ModelsCollector<Files>
 */
class FilesCollector extends ModelsCollector
{
	public function __construct(
		protected string|null $blueprint = null,
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|File|null $parent = null,
		protected string|null $query = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
	) {
	}

	protected function collect(): Files
	{
		return $this->parent()->files()->sorted();
	}

	protected function collectByQuery(): Files
	{
		$files = $this->parent()->query($this->query);

		// if the query only returns a site, page or user object
		// instead of a files collection, use its files
		if (
			$files instanceof Site ||
			$files instanceof Page ||
			$files instanceof User
		) {
			return $files->files()->sorted();
		}

		if ($files instanceof Files) {
			return $files;
		}

		return new Files([]);
	}

	protected function filter(Files|Pages|Users $models): Files
	{
		return $models->filter(function ($file) {
			// remove all protected and hidden files
			if ($file->isListable() === false) {
				return false;
			}

			// filter by blueprint
			if ($this->blueprint !== null && $file->template() !== $this->blueprint) {
				return false;
			}

			return true;
		});
	}
}
