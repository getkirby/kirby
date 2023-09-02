<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\UsersPicker
 */
class UsersPickerTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com'],
				['email' => 'c@getkirby.com']
			]
		]);

		$this->app->impersonate('kirby');
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$picker = new UsersPicker();

		$this->assertCount(3, $picker->items());
	}

	/**
	 * @covers ::items
	 */
	public function testItemsQuery()
	{
		$picker = new UsersPicker([
			'query' => 'kirby.users.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}
}
