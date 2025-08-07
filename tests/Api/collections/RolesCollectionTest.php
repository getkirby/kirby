<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;

class RolesCollectionTest extends CollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.RolesApiCollection';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				[
					'name' => 'admin',
				],
				[
					'name' => 'editor',
				]
			]
		]);

		$this->api = $this->app->api();
		Dir::make(static::TMP);
	}

	public function testCollection(): void
	{
		$collection = $this->api->collection('roles', $this->app->roles());
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('admin', $result[0]['name']);
		$this->assertSame('editor', $result[1]['name']);
	}
}
