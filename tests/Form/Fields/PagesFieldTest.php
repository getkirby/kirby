<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

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
        $field = new Field('pages', [
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
        $field = new Field('pages', [
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
        $field = new Field('pages', [
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
        $field = new Field('pages', [
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
        $field = new Field('pages', [
            'model' => new Page(['slug' => 'test']),
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testTranslatedEmpty()
    {
        $field = new Field('pages', [
            'model' => new Page(['slug' => 'test']),
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testRequiredProps()
    {
        $field = new Field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = new Field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = new Field('pages', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true,
            'value' => [
                'a/aa',
            ],
        ]);

        $this->assertTrue($field->isValid());
    }
}
