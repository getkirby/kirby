<?php

namespace Kirby\Form\Fields;

use Kirby\Form\Field;
use Kirby\Toolkit\I18n;

class TagsFieldTest extends TestCase
{

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

        $field = new Field([
            'name'    => 'tags',
            'type'    => 'tags',
            'options' => 'query',
            'query'   => 'page.siblings.pluck("tags", ",", true)',
        ], [
            'model' => $app->page('b')
        ]);

        $this->assertEquals($expected, $field->options());


        $field = new Field([
            'name'    => 'tags',
            'type'    => 'tags',
            'options' => 'query',
            'query'   => 'file.siblings.pluck("tags", ",", true)',
        ], [
            'model' => $app->file('a/b.jpg')
        ]);

        $this->assertEquals($expected, $field->options());

    }

}
