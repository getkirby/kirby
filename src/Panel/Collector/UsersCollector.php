<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\App;
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
class UsersCollector extends ModelsCollector
{
	public function __construct(
		protected bool $flip = false,
		protected int|null $limit = null,
		protected int $page = 1,
		protected Site|Page|User|null $parent = null,
		protected string|null $query = null,
		protected string|null $role = null,
		protected string|null $search = null,
		protected string|null $sortBy = null,
	) {
	}

	protected function collect(): Users
	{
		return App::instance()->users();
	}

	protected function collectByQuery(): Users
	{
		return $this->parent()->query($this->query, Users::class) ?? new Users([]);
	}

	protected function filter(Files|Pages|Users $models): Users
	{
		$user = App::instance()->user();

		if ($user === null) {
			return new Users([]);
		}

		if ($user->role()->permissions()->for('access', 'users') === false) {
			return new Users([]);
		}

		if ($this->role !== null) {
			$models = $models->role($this->role);
		}

		return $models;
	}
}
