<?php

namespace Kirby\Uuid;

class HasUuidsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.HasUuids';

	public function testfindByUuid(): void
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
						'content' => [
							'uuid' => 'my-id-b',
							'related' => 'page://my-id-a'
						],
					]
				]
			]
		]);

		$pages = $app->site()->children();
		$a     = $pages->find('a');
		$b     = $pages->find('b');

		// without schema (= all schema allowed)
		$result = (fn () => $this->findByUuid('page://my-id-b'))->call($pages);
		$this->assertTrue($b->is($result));

		// with correct schema
		$result = (fn () => $this->findByUuid('page://my-id-b', 'page'))->call($pages);
		$this->assertTrue($b->is($result));

		// with @ shortcut
		$result = (fn () => $this->findByUuid('@my-id-b', 'page'))->call($pages);
		$this->assertTrue($b->is($result));

		// with wrong schema
		$result = (fn () => $this->findByUuid('page://my-id-b', 'file'))->call($pages);
		$this->assertNull($result);

		// find reverse
		$result = $pages->findBy('related', $a->uuid());
		$this->assertTrue($b->is($result));
	}
}
