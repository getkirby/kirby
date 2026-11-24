<?php

namespace Kirby\Api;

class TranslationsCollectionTest extends CollectionTestCase
{
	public function testCollection(): void
	{
		$collection = $this->api->collection('translations', $this->app->translations()->filter('id', 'en'));
		$result     = $collection->toArray();

		$this->assertCount(1, $result);
		$this->assertSame('en', $result[0]['id']);
	}
}
