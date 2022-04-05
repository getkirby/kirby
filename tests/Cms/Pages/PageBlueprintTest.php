<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageBlueprintTest extends TestCase
{
    public function testOptions()
    {
        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'changeSlug'     => null,
            'changeStatus'   => null,
            'changeTemplate' => null,
            'changeTitle'    => null,
            'create'         => null,
            'delete'         => null,
            'duplicate'      => null,
            'preview'        => null,
            'read'           => null,
            'sort'           => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

    public function testExtendedOptionsFromString()
    {
        new App([
            'blueprints' => [
                'options/default' => [
                    'changeSlug' => true,
                    'changeTemplate' => false,
                ]
            ]
        ]);

        $blueprint = new PageBlueprint([
            'model'   => new Page(['slug' => 'test']),
            'options' => 'options/default'
        ]);

        $expected = [
            'changeSlug'     => true,
            'changeStatus'   => null,
            'changeTemplate' => false,
            'changeTitle'    => null,
            'create'         => null,
            'delete'         => null,
            'duplicate'      => null,
            'preview'        => null,
            'read'           => null,
            'sort'           => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

    public function testExtendedOptions()
    {
        new App([
            'blueprints' => [
                'options/default' => [
                    'changeSlug' => true,
                    'changeTemplate' => false,
                ]
            ]
        ]);

        $blueprint = new PageBlueprint([
            'model'   => new Page(['slug' => 'test']),
            'options' => [
                'extends' => 'options/default',
                'create'  => false
            ]
        ]);

        $expected = [
            'changeSlug'     => true,
            'changeStatus'   => null,
            'changeTemplate' => false,
            'changeTitle'    => null,
            'create'         => false,
            'delete'         => null,
            'duplicate'      => null,
            'preview'        => null,
            'read'           => null,
            'sort'           => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

    public function numProvider()
    {
        return [
            ['default', 'default'],
            ['sort', 'default'],
            ['zero', 'zero'],
            [0, 'zero'],
            ['0', 'zero'],
            ['date', 'date'],
            ['datetime', 'datetime'],
            ['{{ page.something }}', '{{ page.something }}'],
        ];
    }

    /**
     * @dataProvider numProvider
     */
    public function testNum($input, $expected)
    {
        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test']),
            'num'   => $input
        ]);

        $this->assertEquals($expected, $blueprint->num());
    }

    public function testStatus()
    {
        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => [
                'draft'    => 'Draft Label',
                'unlisted' => 'Unlisted Label',
                'listed'   => 'Listed Label'
            ]
        ]);

        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => null
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => null
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => null
            ]
        ];

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testStatusWithCustomText()
    {
        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => 'Draft Text'
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => 'Unlisted Text'
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => 'Listed Text'
            ]
        ];

        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => $expected,
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testStatusTranslations()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $input = [
            'draft' => [
                'label' => ['en' => 'Draft Label'],
                'text'  => ['en' => 'Draft Text']
            ],
            'unlisted' => [
                'label' => ['en' => 'Unlisted Label'],
                'text'  => ['en' => 'Unlisted Text']
            ],
            'listed' => [
                'label' => ['en' => 'Listed Label'],
                'text'  => ['en' => 'Listed Text']
            ]
        ];

        $expected = [
            'draft' => [
                'label' => 'Draft Label',
                'text'  => 'Draft Text'
            ],
            'unlisted' => [
                'label' => 'Unlisted Label',
                'text'  => 'Unlisted Text'
            ],
            'listed' => [
                'label' => 'Listed Label',
                'text'  => 'Listed Text'
            ]
        ];

        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => $input,
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testInvalidStatus()
    {
        $input = [
            'draft'    => 'Draft',
            'unlisted' => 'Unlisted',
            'foo'      => 'Bar'
        ];

        $expected = [
            'draft' => [
                'label' => 'Draft',
                'text'  => null
            ],
            'unlisted' => [
                'label' => 'Unlisted',
                'text'  => null
            ],
        ];

        $blueprint = new PageBlueprint([
            'model'  => new Page(['slug' => 'test']),
            'status' => $input,
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testExtendStatus()
    {
        new App([
            'blueprints' => [
                'status/default' => [
                    'draft'    => [
                        'label' => 'Draft Label',
                        'text'  => null,
                    ],
                    'unlisted' => [
                        'label' => 'Unlisted Label',
                        'text'  => null,
                    ],
                    'listed' => [
                        'label' => 'Listed Label',
                        'text'  => null
                    ]
                ],
            ]
        ]);

        $input = [
            'extends'  => 'status/default',
            'draft'    => [
                'label' => 'Draft',
                'text'  => null,
            ],
            'unlisted' => false,
            'listed' => [
                'label' => 'Published',
                'text'  => null
            ]
        ];

        $expected = [
            'draft' => [
                'label' => 'Draft',
                'text'  => null
            ],
            'listed' => [
                'label' => 'Published',
                'text'  => null
            ],
        ];

        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test']),
            'status' => $input
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    public function testExtendStatusFromString()
    {
        new App([
            'blueprints' => [
                'status/default' => $expected = [
                    'draft'    => [
                        'label' => 'Draft Label',
                        'text'  => null,
                    ],
                    'unlisted' => [
                        'label' => 'Unlisted Label',
                        'text'  => null,
                    ],
                    'listed' => [
                        'label' => 'Listed Label',
                        'text'  => null
                    ]
                ],
            ]
        ]);

        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test']),
            'status' => 'status/default'
        ]);

        $this->assertEquals($expected, $blueprint->status());
    }

    /**
     * @covers ::extend
     */
    public function testExtendNum()
    {
        new App([
            'blueprints' => [
                'pages/test' => [
                    'title' => 'Extension Test',
                    'num' => 'date'
                ]
            ]
        ]);

        $blueprint = new PageBlueprint([
            'extends' => 'pages/test',
            'title' => 'Extended',
            'model'   => new Page(['slug' => 'test'])
        ]);

        $this->assertSame('Extended', $blueprint->title());
        $this->assertSame('date', $blueprint->num());
    }
}
