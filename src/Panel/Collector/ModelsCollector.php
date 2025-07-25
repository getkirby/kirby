<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Pagination;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Users;

abstract class ModelsCollector
{
	protected Files|Pages|Users $all;
	protected Files|Pages|Users $paginated;

	public function __construct(
		protected Site|Page|User $parent,
		protected int|null $limit = null,
		protected int $page = 1,
		protected string|null $query = null,
		protected string|null $template = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
		protected bool $flip = false,
	) {
	}

	public function all(): Files|Pages|Users
	{
		return $this->all ??= $this->collect();
	}

	public function collect(): Files|Pages|Users
	{
		if ($this->query !== null) {
			$models = $this->collectByQuery();
		} else {
			$models = $this->collectByParent();
		}

		$models = $this->filter($models);
		$models = $this->search($models);

		if ($this->isSearching() === false) {
			$models = $this->sort($models);
		}

		return $models;
	}

	abstract protected function collectByParent(): Files|Pages|Users;

	abstract protected function collectByQuery(): Files|Pages|Users;

	abstract protected function filter(Files|Pages|Users $models): Files|Pages|Users;

	public function isSearching(): bool
	{
		return $this->search !== null && $this->search !== '';
	}

	public function paginated(): Files|Pages|Users
	{
		return $this->paginated ??= $this->all()->paginate([
			'limit'  => $this->limit ?? 1000,
			'page'   => $this->page,
			'method' => 'none' // the page is manually provided
		]);
	}

	public function pagination(): Pagination
	{
		return $this->paginated()->pagination();
	}

	protected function search(Files|Pages|Users $models): Files|Pages|Users
	{
		if ($this->isSearching() === true) {
			return $models->search($this->search);
		}

		return $models;
	}

	protected function sort(Files|Pages|Users $models): Files|Pages|Users
	{
		if ($this->sortBy !== null) {
			$models = $models->sort(...$models::sortArgs($this->sortBy));
		}

		if ($this->flip === true) {
			$models = $models->flip();
		}

		return $models;
	}
}
