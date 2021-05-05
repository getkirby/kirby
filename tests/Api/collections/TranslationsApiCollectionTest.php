<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class TranslationsApiCollectionTest extends TestCase
{
    protected $api;
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->api = $this->app->api();
    }

    public function testCollection()
    {
        $collection = $this->api->collection('translations', $this->app->translations()->filter('id', 'en'));
        $result     = $collection->toArray();

        $this->assertCount(1, $result);
        $this->assertEquals('en', $result[0]['id']);
    }
}
