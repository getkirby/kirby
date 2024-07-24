<?php

namespace Kirby\Uuid;

class PermalinksTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Uuid.Permalinks';

	public function testRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id']
					],
					[
						'slug'    => 'b',
						'content' => ['uuid' => 'my-other-id']
					]
				]
			]
		]);

		$this->assertTrue(Uuids::cache()->isEmpty());
		$uuid = $app->page('a')->uuid();

		// not cached, but cache is empty => using index to find it
		$this->assertFalse($uuid->isCached());
		$response = $app->call('/@/page/my-id');
		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com/a', $response->header('Location'));

		// now cached, redirect from cache
		$this->assertTrue($uuid->isCached());
		$response = $app->call('/@/page/my-id');
		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com/a', $response->header('Location'));

		// not cached but cache isn't empty => fail to prevent attacks
		$uuid->clear();
		$app->page('b')->uuid()->populate();
		$this->assertFalse($uuid->isCached());
		$this->assertFalse(Uuids::cache()->isEmpty());

		$response = $app->call('/@/page/my-id');
		$this->assertSame(false, $response);
	}
}
