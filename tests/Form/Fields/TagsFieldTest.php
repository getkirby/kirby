<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;
use Kirby\Toolkit\I18n;

class TagsFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('tags');

        $this->assertEquals('tags', $field->type());
        $this->assertEquals('tags', $field->name());
        $this->assertEquals('all', $field->accept());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->default());
        $this->assertEquals([], $field->options());
        $this->assertEquals(null, $field->min());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(',', $field->separator());
        $this->assertEquals('tag', $field->icon());
        $this->assertEquals(null, $field->counter());
        $this->assertTrue($field->save());
    }

    public function testOptionsQuery()
    {
        $app = $this->app()->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content'  => [
                            'tags' => 'design'
                        ],
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                                'content'  => [
                                    'tags' => 'design'
                                ]
                            ],
                            [
                                'filename' => 'b.jpg',
                                'content'  => [
                                    'tags' => 'design, photography'
                                ]
                            ],
                            [
                                'filename' => 'c.jpg',
                                'content'  => [
                                    'tags' => 'design, architecture'
                                ]
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b',
                        'content'  => [
                            'tags' => 'design, photography'
                        ],
                    ],
                    [
                        'slug' => 'c',
                        'content'  => [
                            'tags' => 'design, architecture'
                        ],
                    ]
                ]
            ]
        ]);

        $expected = [
            [
                'value' => 'design',
                'text'  => 'design'
            ],
            [
                'value' => 'photography',
                'text'  => 'photography'
            ],
            [
                'value' => 'architecture',
                'text'  => 'architecture'
            ]
        ];

        $field = new Field('tags', [
            'model'   => $app->page('b'),
            'options' => 'query',
            'query'   => 'page.siblings.pluck("tags", ",", true)',
        ]);

        $this->assertEquals($expected, $field->options());

        $field = new Field('tags', [
            'model'   => $app->file('a/b.jpg'),
            'options' => 'query',
            'query'   => 'file.siblings.pluck("tags", ",", true)',
        ]);

        $this->assertEquals($expected, $field->options());
    }

    public function testMin()
    {
        $field = new Field('tags', [
            'value'   => 'a',
            'options' => ['a', 'b', 'c'],
            'min'     => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
        $this->assertEquals(2, $field->min());
        $this->assertTrue($field->required());
    }

    public function testMax()
    {
        $field = new Field('tags', [
            'value'   => 'a, b',
            'options' => ['a', 'b', 'c'],
            'max'     => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(1, $field->max());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testRequiredProps()
    {
        $field = new Field('tags', [
            'options'  => ['a', 'b', 'c'],
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = new Field('tags', [
            'options'  => ['a', 'b', 'c'],
            'value'    => null,
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = new Field('tags', [
            'options'  => ['a', 'b', 'c'],
            'required' => true,
            'value'    => 'a'
        ]);

        $this->assertTrue($field->isValid());
    }
}
