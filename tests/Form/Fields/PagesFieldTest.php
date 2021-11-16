<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;

class PagesFieldTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'aa',
                            ],
                            [
                                'slug' => 'ab',
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b',
                    ]
                ]
            ]
        ]);
    }

    public function model()
    {
        return $this->app->page('a');
    }

    public function testDefaultProps()
    {
        $field = $this->field('pages', [
            'model' => $this->model()
        ]);

        $this->assertEquals('pages', $field->type());
        $this->assertEquals('pages', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->default());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(true, $field->multiple());
        $this->assertTrue($field->save());
    }

    public function testValue()
    {
        $field = $this->field('pages', [
            'model' => $this->model(),
            'value' => [
                'a/aa', // exists
                'a/ab', // exists
                'a/ac'  // does not exist
            ]
        ]);

        $value = $field->value();
        $ids   = array_column($value, 'id');

        $expected = [
            'a/aa',
            'a/ab'
        ];

        $this->assertEquals($expected, $ids);
    }

    public function testMin()
    {
        $field = $this->field('pages', [
            'model' => $this->model(),
            'value' => [
                'a/aa', // exists
                'a/ab', // exists
            ],
            'min' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(3, $field->min());
        $this->assertTrue($field->required());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('pages', [
            'model' => $this->model(),
            'value' => [
                'a/aa', // exists
                'a/ab', // exists
            ],
            'max' => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(1, $field->max());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testEmpty()
    {
        $field = $this->field('pages', [
            'model' => new Page(['slug' => 'test']),
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testTranslatedEmpty()
    {
        $field = $this->field('pages', [
            'model' => new Page(['slug' => 'test']),
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testRequiredProps()
    {
        $field = $this->field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true,
            'value' => [
                'a/aa',
            ],
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testApi()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => ['api.allowImpersonation' => true],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'title' => 'Test Title',
                        ],
                        'blueprint' => [
                            'title' => 'Test',
                            'name' => 'test',
                            'fields' => [
                                'related' => [
                                    'type' => 'pages',
                                ]
                            ]
                        ]
                    ],
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                    ['slug' => 'c'],
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $api = $app->api()->call('pages/test/fields/related');

        $this->assertCount(3, $api);
        $this->assertArrayHasKey('data', $api);
        $this->assertArrayHasKey('pagination', $api);
        $this->assertArrayHasKey('model', $api);
        $this->assertCount(4, $api['data']);
        $this->assertSame('test', $api['data'][0]['id']);
        $this->assertSame([
            'id' => 'test',
            'image' => [
                'back' => 'pattern',
                'color' => 'gray-500',
                'cover' => false,
                'icon' => 'page',
                'ratio' => '3/2'
            ],
            'info' => '',
            'link' => '/pages/test',
            'sortable' => true,
            'text' => 'Test Title',
            'dragText' => '(link: test text: Test Title)',
            'hasChildren' => false,
            'url' => '/test',
        ], $api['data'][0]);
        $this->assertSame('a', $api['data'][1]['id']);
        $this->assertSame('b', $api['data'][2]['id']);
        $this->assertSame('c', $api['data'][3]['id']);
    }
}
