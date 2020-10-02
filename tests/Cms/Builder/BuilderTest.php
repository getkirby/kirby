<?php

namespace Kirby\Cms;

use Kirby\Data\Yaml;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    protected $app;
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'fieldsets/seo' => [
                    'title' => 'Seo',
                    'model' => 'page',
                    'fields' => [
                        'metaTitle' => [
                            'label' => 'Meta Title',
                            'type' => 'text'
                        ],
                        'meta' => 'fields/meta'
                    ]
                ],
                'fieldsets/heading' => [
                    'title' => 'Heading',
                    'model' => 'page',
                    'fields' => [
                        'text' => [
                            'label' => 'Text',
                            'type' => 'text'
                        ],
                    ]
                ],
                'fieldsets/events' => [
                    'title' => 'Events',
                    'model' => 'page',
                    'fields' => [
                        'eventList' => [
                            'label' => 'Event List',
                            'type' => 'builder',
                            'fieldsets' => [
                                'event' => 'fields/event',
                                'speaker' => 'fields/speaker'
                            ]
                        ],
                    ]
                ],
                'fields/meta' => [
                    'type' => 'group',
                    'fields' => [
                        'metaDescription' => [
                            'label' => 'Meta Description',
                            'type' => 'textarea'
                        ],
                        'metaKeywords' => [
                            'label' => 'Meta Keywords',
                            'type' => 'text'
                        ]
                    ]
                ],
                'fields/event' => [
                    'title' => [
                        'label' => 'Event Title',
                        'type' => 'text'
                    ],
                ],
                'fields/speaker' => [
                    'name' => [
                        'label' => 'Speaker Name',
                        'type' => 'text'
                    ],
                ]
            ]
        ]);

        $this->page = new Page(['slug' => 'test']);
    }

    public function testFieldsProps()
    {
        $builder = new Builder($this->page);
        $result  = $builder->fieldsProps([
            'text' => [
                'type'  => 'textarea'
            ],
            'citation' => [
                'label' => 'Quote Citation',
                'type'  => 'text'
            ]
        ]);

        $this->assertArrayHasKey('signature', $result['text']);
        $this->assertArrayHasKey('signature', $result['citation']);

        // check for a proper textarea with automatic label
        $this->assertSame('text', $result['text']['name']);
        $this->assertSame('Text', $result['text']['label']);

        // check for a proper text input with custom label
        $this->assertSame('citation', $result['citation']['name']);
        $this->assertSame('Quote Citation', $result['citation']['label']);
    }

    public function testFieldsetProps()
    {
        $builder  = new Builder($this->page);
        $fieldset = $builder->fieldsetProps('quote', []);

        $this->assertSame('quote', $fieldset['type']);
        $this->assertSame('Quote', $fieldset['name']);
        $this->assertSame('Quote', $fieldset['label']);
        $this->assertSame([], $fieldset['tabs']['content']['fields']);
    }

    public function testFieldsets()
    {
        $builder = new Builder($this->page, [
            'fieldsets' => [
                'quote' => [
                    'name' => 'Quote',
                    'icon' => 'quote'
                ],
                'bodytext' => [
                    'name' => 'Text'
                ]
            ]
        ]);

        $this->assertSame([
            'quote' => [
                'disabled'  => false,
                'icon'      => 'quote',
                'label'     => 'Quote',
                'name'      => 'Quote',
                'tabs' => [
                    'content' => [
                        'fields' => []
                    ]
                ],
                'translate' => null,
                'type'      => 'quote',
                'unset'     => false
            ],
            'bodytext' => [
                'disabled'  => false,
                'icon'      => null,
                'label'     => 'Text',
                'name'      => 'Text',
                'tabs' => [
                    'content' => [
                        'fields' => []
                    ]
                ],
                'translate' => null,
                'type'      => 'bodytext',
                'unset'     => false
            ]
        ], $builder->fieldsets());
    }

    public function testNestedStructure()
    {
        $fieldsets = [
            'table' => [
                'fields' => [
                    'rows' => [
                        'type' => 'structure',
                        'fields' => [
                            'header' => [
                                'type' => 'text'
                            ],
                            'value' => [
                                'type' => 'text'
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $rows = [
            [
                'header' => 'Header A',
                'value'  => 'Value A'
            ],
            [
                'header' => 'Header B',
                'value'  => 'Value B'
            ]
        ];

        $builder = new Builder($this->page, ['fieldsets' => $fieldsets]);

        // with yaml
        $value = [
            [
                'type' => 'table',
                'content' => [
                    'rows' => Yaml::encode($rows)
                ]
            ]
        ];

        $blocks    = $builder->blocks($value);
        $structure = $blocks->first()->rows()->toStructure();

        $this->assertInstanceOf('Kirby\Cms\Structure', $structure);
        $this->assertEquals('Header A', $structure->first()->header());
        $this->assertEquals('Value A', $structure->first()->value());
        $this->assertEquals('Header B', $structure->last()->header());
        $this->assertEquals('Value B', $structure->last()->value());

        // with an array
        $value = [
            [
                'type' => 'table',
                'content' => [
                    'rows' => $rows
                ]
            ]
        ];

        $blocks    = $builder->blocks($value);
        $structure = $blocks->first()->rows()->toStructure();

        $this->assertInstanceOf('Kirby\Cms\Structure', $structure);
        $this->assertEquals('Header A', $structure->first()->header());
        $this->assertEquals('Value A', $structure->first()->value());
        $this->assertEquals('Header B', $structure->last()->header());
        $this->assertEquals('Value B', $structure->last()->value());
    }

    public function testValidation()
    {
        $fieldsets = [
            'heading' => [
                'fields' => [
                    'text' => [
                        'type' => 'text',
                        'required' => true
                    ]
                ]
            ]
        ];

        $builder = new Builder($this->page, ['fieldsets' => $fieldsets]);

        // valid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'awesome'
                ]
            ]
        ];

        $this->assertTrue($builder->validate($input));

        // invalid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => ''
                ]
            ]
        ];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('There\'s an error in block 1');

        $builder->validate($input);
    }

    public function testValidationWithConditionalFields()
    {
        $fieldsets = [
            'heading' => [
                'fields' => [
                    'text' => [
                        'type' => 'text',
                        'required' => true,
                        'when' => [
                            'toggle' => true
                        ]
                    ],
                    'toggle' => [
                        'type' => 'toggle'
                    ]
                ]
            ]
        ];

        $builder = new Builder($this->page, ['fieldsets' => $fieldsets]);

        // valid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => ''
                ]
            ]
        ];

        $this->assertTrue($builder->validate($input));

        // also valid when toggle is on an field is filled in
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'Some content',
                    'toggle' => true
                ]
            ]
        ];

        $this->assertTrue($builder->validate($input));

        // invalid when toggle is on an field is empty
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => '',
                    'toggle' => true
                ]
            ]
        ];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('There\'s an error in block 1');

        $builder->validate($input);
    }

    public function testValidationMax()
    {
        $fieldsets = [
            'heading' => [
                'fields' => [
                    'text' => [
                        'type' => 'text',
                    ]
                ]
            ]
        ];

        $builder = new Builder($this->page, [
            'fieldsets' => $fieldsets,
            'max'       => 1
        ]);

        // valid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'awesome'
                ]
            ]
        ];

        $this->assertTrue($builder->validate($input));

        // invalid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'Heading 1'
                ]
            ],
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'Heading 2'
                ]
            ]
        ];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You must not add more than one block');

        $builder->validate($input);
    }

    public function testValidationMin()
    {
        $fieldsets = [
            'heading' => [
                'fields' => [
                    'text' => [
                        'type' => 'text',
                    ]
                ]
            ]
        ];

        $builder = new Builder($this->page, [
            'fieldsets' => $fieldsets,
            'min'       => 1
        ]);

        // valid
        $input = [
            [
                'type' => 'heading',
                'content' => [
                    'text' => 'awesome'
                ]
            ]
        ];

        $this->assertTrue($builder->validate($input));

        // invalid
        $input = [];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You must add at least one block');

        $builder->validate($input);
    }

    public function testValue()
    {
        $fieldsets = [
            'quote' => [
                'name' => 'Quote',
                'type' => 'text',
                'fields' => [
                    'quote'    => ['type' => 'text'],
                    'citation' => ['type' => 'textarea']
                ]
            ],
            'bodytext' => [
                'name' => 'Text',
                'type' => 'textarea',
                'fields' => [
                    'text' => ['type' => 'textarea'],
                ]
            ]
        ];

        // empty builder - no defaults
        $builder = new Builder($this->page, ['fieldsets' => $fieldsets]);
        $this->assertSame([], $builder->value());

        // with empty input
        $builder = new Builder($this->page, [
            'fieldsets' => $fieldsets,
            'value' => [
                ['type' => 'quote'],
                ['type' => 'bodytext'],
            ]
        ]);

        $this->assertSame('', $builder->value()[0]['content']['quote']);
        $this->assertSame('', $builder->value()[0]['content']['citation']);
        $this->assertArrayHasKey('type', $builder->value()[0]);
        $this->assertArrayHasKey('id', $builder->value()[0]);

        $this->assertSame('', $builder->value()[1]['content']['text']);
        $this->assertArrayHasKey('type', $builder->value()[1]);
        $this->assertArrayHasKey('id', $builder->value()[1]);
    }

    public function testExtend()
    {
        $builder = new Builder($this->page, ['fieldsets' => [
            'seo' => 'fieldsets/seo',
            'heading' => 'fieldsets/heading',
        ]]);

        $fieldsets = $builder->fieldsets();

        $this->assertArrayHasKey('heading', $fieldsets);
        $this->assertArrayHasKey('tabs', $fieldsets['heading']);
        $this->assertArrayHasKey('text', $fieldsets['heading']['tabs']['content']['fields']);

        $this->assertArrayHasKey('seo', $fieldsets);
        $this->assertArrayHasKey('tabs', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayHasKey('metadescription', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayHasKey('metakeywords', $fieldsets['seo']['tabs']['content']['fields']);
    }

    public function testExtendNestedBuilder()
    {
        $builder = new Builder($this->page, ['fieldsets' => [
            'events' => [
                'extends' => 'fieldsets/events'
            ]
        ]]);

        $fieldsets = $builder->fieldsets();

        $this->assertArrayHasKey('events', $fieldsets);
        $this->assertArrayHasKey('tabs', $fieldsets['events']);
        $this->assertArrayHasKey('eventlist', $fieldsets['events']['tabs']['content']['fields']);
        $this->assertInstanceOf('\Kirby\Cms\Builder', $fieldsets['events']['tabs']['content']['fields']['eventlist']['builder']);
    }

    public function testExtendUnsetFieldsetFields()
    {
        $builder = new Builder($this->page, ['fieldsets' => [
            'seo' => [
                'extends' => 'fieldsets/seo',
                'fields' => [
                    'metaDescription' => false,
                    'metaKeywords' => false
                ],
            ],
        ]]);

        $fieldsets = $builder->fieldsets();

        $this->assertArrayHasKey('seo', $fieldsets);
        $this->assertArrayHasKey('tabs', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayNotHasKey('metadescription', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayNotHasKey('metakeywords', $fieldsets['seo']['tabs']['content']['fields']);
    }

    public function testExtendUnsetFields()
    {
        $builder = new Builder($this->page, ['fieldsets' => [
            'seo' => [
                'fields' => [
                    'metaTitle' => [
                        'label' => 'Meta Title',
                        'type' => 'text'
                    ],
                    'meta' => [
                        'extends' => 'fields/meta',
                        'fields' => [
                            'metaKeywords' => false
                        ]
                    ]
                ]
            ],
        ]]);

        $fieldsets = $builder->fieldsets();

        $this->assertArrayHasKey('seo', $fieldsets);
        $this->assertArrayHasKey('tabs', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayHasKey('metadescription', $fieldsets['seo']['tabs']['content']['fields']);
        $this->assertArrayNotHasKey('metakeywords', $fieldsets['seo']['tabs']['content']['fields']);
    }
}
