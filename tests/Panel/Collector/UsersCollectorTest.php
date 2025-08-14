<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Users;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersCollector::class)]
class UsersCollectorTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Collector.UsersCollector';

	public function setUpUsers()
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name' => 'admin'
				],
				[
					'name' => 'editor'
				],
			],
			'users' => [
				[
					'id'    => 'a',
					'email' => 'a@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'b',
					'email' => 'b@getkirby.com',
					'role'  => 'editor',
				],
				[
					'id'    => 'c',
					'email' => 'c@getkirby.com',
					'role'  => 'editor',
				],
			]
		]);
	}

	public function testCollect(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');
		$this->assertCollect(UsersCollector::class, ['a', 'b', 'c']);
	}

	public function testCollectByQuery(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');
		$this->assertCollectByQuery(UsersCollector::class, 'kirby.users.role("editor")', ['b', 'c']);
	}

	public function testCollectUnauthenticated(): void
	{
		$this->setUpUsers();
		$this->assertCollectUnauthenticated(UsersCollector::class);
	}

	public function testCollectWithoutAccess(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('nobody');
		$this->assertCollectUnauthenticated(UsersCollector::class);
	}

	public function testFilterByRole(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');

		$collector = new UsersCollector(
			role: 'editor'
		);

		$this->assertModelsInCollector($collector, ['b', 'c'], );
	}

	public function testFlip(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');

		$this->assertFlip(UsersCollector::class, ['c', 'b', 'a']);
	}

	public function testIsFlipping(): void
	{
		$this->assertIsFlipping(UsersCollector::class);
	}

	public function testIsQuerying(): void
	{
		$this->assertIsQuerying(UsersCollector::class);
	}

	public function testIsSearching(): void
	{
		$this->assertIsSearching(UsersCollector::class);
	}

	public function testIsSorting(): void
	{
		$this->assertIsSorting(UsersCollector::class);
	}

	public function testModels(): void
	{
		$this->assertModels(UsersCollector::class, Users::class);
	}

	public function testPagination(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');
		$this->assertPagination(UsersCollector::class);
	}

	public function testSearch(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');
		$this->app->users()->find('c@getkirby.com')->update([
			'text' => 'Searchword'
		]);

		$this->assertSearch(UsersCollector::class, 'Searchword', ['c']);
	}

	public function testSearchAndFlip(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');

		$this->app->users()->find('b@getkirby.com')->update([
			'title' => 'Searchword'
		]);

		$this->app->users()->find('c@getkirby.com')->update([
			'title' => 'Searchword'
		]);

		// flipping should be ignored for the search
		$this->assertSearchAndFlip(UsersCollector::class, 'Searchword', ['b', 'c']);
	}

	public function testSearchAndSortBy(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');

		$this->app->users()->find('b@getkirby.com')->update([
			'title' => 'Searchword'
		]);

		$this->app->users()->find('c@getkirby.com')->update([
			'title' => 'Searchword'
		]);

		// the sorting should be ignored for the search
		$this->assertSearchAndSortBy(UsersCollector::class, 'Searchword', 'email desc', ['b', 'c']);
	}

	public function testSortBy(): void
	{
		$this->setUpUsers();
		$this->app->impersonate('kirby');
		$this->assertSortBy(UsersCollector::class, 'email desc', ['c', 'b', 'a']);
	}
}
