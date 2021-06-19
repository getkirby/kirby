<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;

class TagsFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('tags');

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

        $field = $this->field('tags', [
            'model'   => $app->page('b'),
            'options' => 'query',
            'query'   => 'page.siblings.pluck("tags", ",", true)',
        ]);

        $this->assertEquals($expected, $field->options());

        $field = $this->field('tags', [
            'model'   => $app->file('a/b.jpg'),
            'options' => 'query',
            'query'   => 'file.siblings.pluck("tags", ",", true)',
        ]);

        $this->assertEquals($expected, $field->options());
    }

    public function testMin()
    {
        $field = $this->field('tags', [
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
        $field = $this->field('tags', [
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
        $field = $this->field('tags', [
            'options'  => ['a', 'b', 'c'],
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('tags', [
            'options'  => ['a', 'b', 'c'],
            'value'    => null,
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('tags', [
            'options'  => ['a', 'b', 'c'],
            'required' => true,
            'value'    => 'a'
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testDefault()
    {
        $field = $this->field('tags', [
            'options'  => ['a', 'b', 'c'],
            'default' => '{{ page.test }}',
            'model' => new Page([
                'slug' => 'foo',
                'content' => [
                    'title' => 'Foo',
                    'test' => 'a,b,c'
                ]
            ])
        ]);

        $this->assertSame([
            [
                'value' => 'a',
                'text' => 'a',
            ],
            [
                'value' => 'b',
                'text' => 'b',
            ],
            [
                'value' => 'c',
                'text' => 'c',
            ]
        ], $field->default());
    }
}
