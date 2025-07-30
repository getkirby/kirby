<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Users;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PagesCollector extends ModelsCollector
{
	public function __construct(
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $status = null,
		protected array $templates = [],
		protected array $templatesIgnore = [],
		protected string|null $search = null,
		protected string|null $sortBy = null,
		protected bool $flip = false,
	) {
	}

	protected function collect(): Pages
	{
		return match ($this->status) {
			'draft'     => $this->parent()->drafts(),
			'listed'    => $this->parent()->children()->listed(),
			'published' => $this->parent()->children(),
			'unlisted'  => $this->parent()->children()->unlisted(),
			default     => $this->parent()->childrenAndDrafts()
		};
	}

	protected function collectByQuery(): Pages
	{
		return $this->parent()->query($this->query, Pages::class) ?? new Pages([]);
	}

	protected function filter(Files|Pages|Users $models): Pages
	{
		// filters pages that are protected and not in the templates list
		// internal `filter()` method used instead of foreach loop that previously included `unset()`
		// because `unset()` is updating the original data, `filter()` is just filtering
		// also it has been tested that there is no performance difference
		// even in 0.1 seconds on 100k virtual pages
		return $models->filter(function (Page $model): bool {
			// remove all protected and hidden pages
			if ($model->isListable() === false) {
				return false;
			}

			$intendedTemplate = $model->intendedTemplate()->name();

			// filter by all set templates
			if (
				$this->templates &&
				in_array($intendedTemplate, $this->templates, true) === false
			) {
				return false;
			}

			// exclude by all ignored templates
			if (
				$this->templatesIgnore &&
				in_array($intendedTemplate, $this->templatesIgnore, true) === true
			) {
				return false;
			}

			return true;
		});
	}
}
