<?php

namespace Kirby\Api;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testConstruct()
    {
        $api = new Api([]);
        $collection = new Collection($api, [], []);

        $this->assertInstanceOf('Kirby\Api\Collection', $collection);
    }

    public function testSelect()
    {
        $api = new Api([
            'models' => [
                'test' => [
                    'fields' => [
                        'key' => function ($model) {
                            return strtolower($model);
                        },
                        'value' => function ($model) {
                            return $model;
                        }
                    ]
                ]
            ]
        ]);

        $collection = new Collection($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model' => 'test'
        ]);

        // success
        $result = $collection->select('key')->toArray();

        $this->assertCount(3, $result);
        $this->assertSame(['key' => 'a'], $result[0]);
        $this->assertSame(['key' => 'b'], $result[1]);
        $this->assertSame(['key' => 'c'], $result[2]);

        // invalid select
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid select keys');
        $collection->select(0)->toArray();
    }

    public function testToArray()
    {
        $api = new Api([
            'models' => [
                'test' => [
                    'fields' => [
                        'value' => function ($model) {
                            return $model;
                        }
                    ]
                ]
            ]
        ]);
        $collection = new Collection($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model' => 'test'
        ]);

        $result = $collection->toArray();

        $this->assertCount(3, $result);
        $this->assertSame(['value' => 'A'], $result[0]);
        $this->assertSame(['value' => 'B'], $result[1]);
        $this->assertSame(['value' => 'C'], $result[2]);
    }

    public function testToResponse()
    {
        $api = new Api([
            'models' => [
                'test' => [
                    'type'   => '\Kirby\Cms\Page',
                    'fields' => [
                        'value' => function ($model) {
                            return $model->slug();
                        }
                    ]
                ]
            ]
        ]);
        $collection = new Collection($api, new Pages([
            new Page(['slug' => 'a']),
            new Page(['slug' => 'b']),
            new Page(['slug' => 'c']),
        ]), [
            'model' => 'test'
        ]);

        $result = $collection->toResponse();

        $this->assertSame(200, $result['code']);
        $this->assertSame('ok', $result['status']);
        $this->assertSame('collection', $result['type']);
        $this->assertSame([
            ['value' => 'a'],
            ['value' => 'b'],
            ['value' => 'c']
        ], $result['data']);
        $this->assertSame([
            'page'   => 1,
            'total'  => 3,
            'offset' => 0,
            'limit'  => 100
        ], $result['pagination']);
    }
}
