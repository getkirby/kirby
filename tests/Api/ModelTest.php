<?php

namespace Kirby\Api;

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testConstruct()
    {
        $model = new Model(new Api([]), [], []);

        $this->assertInstanceOf('Kirby\Api\Model', $model);
    }
    public function testConstructInvalidModel()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid model type "stdClass" expected: "nonexists"');

        new Model(new Api([]), new \stdClass(), ['type' => 'nonexists']);
    }

    public function testConstructMissingModel()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing model data');

        new Model(new Api([]), null, []);
    }

    public function testSelectInvalidKeys()
    {
        $model = new Model(new Api([]), [], []);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid select keys');
        $model->select(0);
    }

    public function testSelection()
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

        // invalid select
        $model = new Model($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model'  => 'test',
            'select' => ['key']
        ]);

        $selection = $model->selection();

        $this->assertSame(['key' => [
            'view'   => null,
            'select' => null
        ]], $selection);

        // string select
        $model = new Model($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model'  => 'test',
            'select' => ['key' => 'value']
        ]);

        $selection = $model->selection();

        $this->assertSame(['key' => [
            'view'   => 'value',
            'select' => null
        ]], $selection);

        // array select
        $model = new Model($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model'  => 'test',
            'select' => ['key' => ['key', 'value']]
        ]);

        $selection = $model->selection();

        $this->assertSame(['key' => [
            'view'   => null,
            'select' => ['key', 'value']
        ]], $selection);

        // invalid view select
        $model = new Model($api, [
            'foo' => 'A',
            'bar' => 'B',
            'baz' => 'C',
        ], [
            'model'  => 'test',
            'select' => ['key' => 'any']
        ]);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid sub view: "any"');

        $selection = $model->selection();
    }
}
