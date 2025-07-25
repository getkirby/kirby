<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\App;
use Kirby\Cms\Files;
use Kirby\Cms\Pages;
use Kirby\Cms\Users;

class UsersCollector extends ModelsCollector
{
	public function __construct(
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected string|null $query = null,
		protected string|null $role = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
	) {
	}

	protected function collectByParent(): Users
	{
		return App::instance()->users();
	}

	protected function collectByQuery(): Users
	{
		return $this->parent->query($this->query, Users::class) ?? new Users([]);
	}

	protected function filter(Files|Pages|Users $models): Users
	{
		if ($this->role !== null) {
			$models = $models->role($this->role);
		}

		return $models;
	}
}
