<?php

namespace Kirby\Panel\Collector;

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
 * @extends ModelsCollector<Pages>
 */
class PagesCollector extends ModelsCollector
{
	/**
	 * @param array{only?: list<string>|null, except?: list<string>} $blueprints Lists `only` pages with these blueprints (or with any for `null`) and none with the blueprints in `except`
	 */
	public function __construct(
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $status = null,
		protected array $blueprints = [],
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
		$only   = $this->blueprints['only'] ?? null;
		$except = $this->blueprints['except'] ?? [];

		return $models->filter(function (Page $model) use ($only, $except): bool {
			// remove all protected and hidden pages
			if ($model->isListable() === false) {
				return false;
			}

			$blueprint = $model->intendedTemplate()->name();

			// filter by the listed blueprints
			if ($only !== null && in_array($blueprint, $only, true) === false) {
				return false;
			}

			// exclude the blueprints in `except`
			if (in_array($blueprint, $except, true) === true) {
				return false;
			}

			return true;
		});
	}
}
