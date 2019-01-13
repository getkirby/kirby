<?php

namespace Kirby\Cms;

class CollectionsTest extends TestCase
{
    public function testGet()
    {
        $collection  = new Collection();
        $collections = new Collections([
            'test' => function () use ($collection) {
                return $collection;
            }
        ]);

        $result = $collections->get('test');

        $this->assertEquals($collection, $result);
    }

    public function testGetWithData()
    {
        $collections = new Collections([
            'test' => function ($a, $b) {
                return $a . $b;
            }
        ]);

        $result = $collections->get('test', [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals('ab', $result);
    }

    public function testGetWithRearrangedData()
    {
        $collections = new Collections([
            'test' => function ($b, $a) {
                return $a . $b;
            }
        ]);

        $result = $collections->get('test', [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals('ab', $result);
    }

    public function testHas()
    {
        $collections = new Collections([
            'test' => function ($b, $a) {
                return $a . $b;
            }
        ]);

        $this->assertTrue($collections->has('test'));
        $this->assertFalse($collections->has('does-not-exist'));
    }

    public function testLoad()
    {
        $app = new App([
            'roots' => [
                'collections' => __DIR__ . '/fixtures/collections'
            ]
        ]);

        $collections = Collections::load($app);
        $result      = $collections->get('test');

        $this->assertInstanceOf(Collection::class, $result);
    }
}
