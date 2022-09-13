<?php

namespace Kirby\Uuid;

class HasUuidsTest extends TestCase
{
	public function testfindByUuid()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id-a'],
					],
					[
						'slug'    => 'b',
						'content' => ['uuid' => 'my-id-b'],
					]
				]
			]
		]);

		$pages = $app->site()->children();
		$b     = $pages->find('b');

		// without schema (= all schema allowed)
		$result = (fn () => $this->findByUuid('page://my-id-b'))->call($pages);
		$this->assertTrue($b->is($result));

		// with correct schema
		$result = (fn () => $this->findByUuid('page://my-id-b', 'page'))->call($pages);
		$this->assertTrue($b->is($result));

		// with wrong schema
		$result = (fn () => $this->findByUuid('page://my-id-b', 'file'))->call($pages);
		$this->assertNull($result);
	}
}
