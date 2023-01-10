<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;
use Kirby\Filesystem\Dir;

class RolesApiCollectionTest extends ApiCollectionTestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
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
		Dir::make($this->tmp);
	}

	public function testCollection()
	{
		$collection = $this->api->collection('roles', $this->app->roles());
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertEquals('admin', $result[0]['name']);
		$this->assertEquals('editor', $result[1]['name']);
	}
}
