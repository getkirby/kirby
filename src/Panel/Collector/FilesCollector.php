<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Users;

class FilesCollector extends ModelsCollector
{
	public function __construct(
		protected Site|Page|User $parent,
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected string|null $query = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
		protected string|null $template = null,
	) {
	}

	protected function collectByParent(): Files
	{
		return $this->parent->files();
	}

	protected function collectByQuery(): Files
	{
		return $this->parent->query($this->query, Files::class) ?? new Files([]);
	}

	protected function filter(Files|Pages|Users $models): Files
	{
		return $models->filter(function ($file) {
			// remove all protected and hidden files
			if ($file->isListable() === false) {
				return false;
			}

			// filter by template
			if ($this->template !== null && $file->template() !== $this->template) {
				return false;
			}

			return true;
		});
	}

	protected function sort(Files|Pages|Users $models): Files|Pages|Users
	{
		if ($this->sortBy === null) {
			$models = $models->sorted();
		}

		return parent::sort($models);
	}
}
