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

    public function testLoad()
    {
        $collections = Collections::load(__DIR__ . '/fixtures/collections');
        $result      = $collections->get('test');

        $this->assertInstanceOf(Collection::class, $result);
    }

}
