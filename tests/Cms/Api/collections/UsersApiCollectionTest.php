<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;
use Kirby\Filesystem\Dir;

class UsersApiCollectionTest extends ApiCollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UsersApiCollection';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com']
			]
		]);

		$this->api = $this->app->api();
		Dir::make(static::TMP);
	}

	public function testDefaultCollection()
	{
		$collection = $this->api->collection('users');
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('a@getkirby.com', $result[0]['email']);
		$this->assertSame('b@getkirby.com', $result[1]['email']);
	}

	public function testPassedCollection()
	{
		$collection = $this->api->collection('users', $this->app->users()->offset(1));
		$result     = $collection->toArray();

		$this->assertCount(1, $result);
		$this->assertSame('b@getkirby.com', $result[0]['email']);
	}
}
