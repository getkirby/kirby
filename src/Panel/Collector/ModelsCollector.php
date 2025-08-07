<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\App;
use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Pagination;
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
abstract class ModelsCollector
{
	protected Files|Pages|Users $models;
	protected Files|Pages|Users $paginated;

	public function __construct(
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
		protected bool $flip = false,
	) {
	}

	abstract protected function collect(): Files|Pages|Users;
	abstract protected function collectByQuery(): Files|Pages|Users;
	abstract protected function filter(Files|Pages|Users $models): Files|Pages|Users;

	protected function flip(Files|Pages|Users $models): Files|Pages|Users
	{
		return $models->flip();
	}

	public function isFlipping(): bool
	{
		if ($this->isSearching() === true) {
			return false;
		}

		return $this->flip === true;
	}

	public function isQuerying(): bool
	{
		return $this->query !== null;
	}

	public function isSearching(): bool
	{
		return $this->search !== null && trim($this->search) !== '';
	}

	public function isSorting(): bool
	{
		if ($this->isSearching() === true) {
			return false;
		}

		return $this->sortBy !== null;
	}

	public function models(bool $paginated = false): Files|Pages|Users
	{
		if ($paginated === true) {
			return $this->paginated ??= $this->models()->paginate([
				'limit'  => $this->limit ?? 1000,
				'page'   => $this->page,
				'method' => 'none' // the page is manually provided
			]);
		}

		if (isset($this->models) === true) {
			return $this->models;
		}

		if ($this->isQuerying() === true) {
			$models = $this->collectByQuery();
		} else {
			$models = $this->collect();
		}

		$models = $this->filter($models);

		if ($this->isSearching() === true) {
			$models = $this->search($models);
		}

		if ($this->isSorting() === true) {
			$models = $this->sort($models);
		}

		if ($this->isFlipping() === true) {
			$models = $this->flip($models);
		}

		return $this->models ??= $models;
	}

	public function pagination(): Pagination
	{
		return $this->models(paginated: true)->pagination();
	}

	protected function parent(): Site|Page|User
	{
		return $this->parent ?? App::instance()->site();
	}

	protected function search(Files|Pages|Users $models): Files|Pages|Users
	{
		return $models->search($this->search);
	}

	protected function sort(Files|Pages|Users $models): Files|Pages|Users
	{
		return $models->sort(...$models::sortArgs($this->sortBy));
	}
}
