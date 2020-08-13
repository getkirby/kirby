<?php

namespace Kirby\Api;

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testConstruct()
    {
        // success
        $api = new Api([]);
        $model = new Model($api, [], []);

        $this->assertInstanceOf('Kirby\Api\Model', $model);

        // invalid model
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid model type "stdClass" expected: "nonexists"');

        $api = new Api([]);
        new Model($api, new \stdClass(), ['type' => 'nonexists']);

        // missing model
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing model data');

        $api = new Api([]);
        new Model($api, null, []);
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

        // success
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
    }
}
