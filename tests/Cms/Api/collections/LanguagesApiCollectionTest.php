<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;
use Kirby\Filesystem\Dir;

class LanguagesApiCollectionTest extends ApiCollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguagesApiCollection';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->api = $this->app->api();
		Dir::make(static::TMP);
	}

	public function testCollection(): void
	{
		$collection = $this->api->collection('languages', $this->app->languages());
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('en', $result[0]['code']);
		$this->assertSame('de', $result[1]['code']);
	}
}
