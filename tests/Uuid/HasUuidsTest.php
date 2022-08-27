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
		$this->assertSame($b, $pages->findByUuid('page://my-id-b'));
		// with correct schema
		$this->assertSame($b, $pages->findByUuid('page://my-id-b', 'page'));
		// with wrong schema
		$this->assertNull($pages->findByUuid('page://my-id-b', 'file'));
	}
}
