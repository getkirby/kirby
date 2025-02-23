<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPicker::class)]
class UserPickerTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserPicker';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com'],
				['email' => 'c@getkirby.com']
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults(): void
	{
		$picker = new UserPicker();

		$this->assertCount(3, $picker->items());
	}

	public function testQuery(): void
	{
		$picker = new UserPicker([
			'query' => 'kirby.users.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}
}
