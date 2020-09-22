<?php

namespace Kirby\Cms;

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

    public function testFields()
    {
        $builder = new Builder($this->page);
        $result  = $builder->fields([
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
    }

    public function testFieldset()
    {
        $builder  = new Builder($this->page);
        $fieldset = $builder->fieldset('quote', []);

        $this->assertSame('quote', $fieldset['type']);
        $this->assertSame('Quote', $fieldset['name']);
        $this->assertSame('Quote', $fieldset['label']);
        $this->assertSame([], $fieldset['fields']);
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
                'fields'    => [],
                'type'      => 'quote',
                'name'      => 'Quote',
                'label'     => 'Quote',
                'icon'      => 'quote',
                'disabled'  => false,
                'translate' => null,
                'unset'     => false
            ],
            'bodytext' => [
                'fields'    => [],
                'type'      => 'bodytext',
                'name'      => 'Text',
                'label'     => 'Text',
                'icon'      => null,
                'disabled'  => false,
                'translate' => null,
                'unset'     => false
            ]
        ], $builder->fieldsets());
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

        // check for a proper textarea with automatic label
        $this->assertSame('text', $result['text']['name']);
        $this->assertSame('Text', $result['text']['label']);

        // check for a proper text input with custom label
        $this->assertSame('citation', $result['citation']['name']);
        $this->assertSame('Quote Citation', $result['citation']['label']);
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
        $this->assertArrayHasKey('fields', $fieldsets['heading']);
        $this->assertArrayHasKey('text', $fieldsets['heading']['fields']);

        $this->assertArrayHasKey('seo', $fieldsets);
        $this->assertArrayHasKey('fields', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['fields']);
        $this->assertArrayHasKey('metadescription', $fieldsets['seo']['fields']);
        $this->assertArrayHasKey('metakeywords', $fieldsets['seo']['fields']);
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
        $this->assertArrayHasKey('fields', $fieldsets['events']);
        $this->assertArrayHasKey('eventlist', $fieldsets['events']['fields']);
        $this->assertInstanceOf('\Kirby\Cms\Builder', $fieldsets['events']['fields']['eventlist']['builder']);
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
        $this->assertArrayHasKey('fields', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['fields']);
        $this->assertArrayNotHasKey('metadescription', $fieldsets['seo']['fields']);
        $this->assertArrayNotHasKey('metakeywords', $fieldsets['seo']['fields']);
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
        $this->assertArrayHasKey('fields', $fieldsets['seo']);
        $this->assertArrayHasKey('metatitle', $fieldsets['seo']['fields']);
        $this->assertArrayHasKey('metadescription', $fieldsets['seo']['fields']);
        $this->assertArrayNotHasKey('metakeywords', $fieldsets['seo']['fields']);
    }
}
