<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;

class TranslationsApiCollectionTest extends ApiCollectionTestCase
{
	public function testCollection()
	{
		$collection = $this->api->collection('translations', $this->app->translations()->filter('id', 'en'));
		$result     = $collection->toArray();

		$this->assertCount(1, $result);
		$this->assertEquals('en', $result[0]['id']);
	}
}
