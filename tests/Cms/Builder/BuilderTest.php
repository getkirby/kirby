<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
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

        $this->assertSame('quote', $fieldset['key']);
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

        $this->assertEquals([
            'quote' => [
                'fields' => [],
                'key'   => 'quote',
                'name'  => 'Quote',
                'label' => 'Quote',
                'icon'  => 'quote'
            ],
            'bodytext' => [
                'fields' => [],
                'key'   => 'bodytext',
                'name'  => 'Text',
                'label' => 'Text',
                'icon'  => null
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
                ['_key' => 'quote'],
                ['_key' => 'bodytext'],
            ]
        ]);

        $expected = [
            [
                'quote' => '',
                'citation' => '',
                '_key' => 'quote'
            ],
            [
                'text' => '',
                '_key' => 'bodytext'
            ]
        ];

        $this->assertSame('', $builder->value()[0]['quote']);
        $this->assertSame('', $builder->value()[0]['citation']);
        $this->assertArrayHasKey('_key', $builder->value()[0]);
        $this->assertArrayHasKey('_uid', $builder->value()[0]);

        $this->assertSame('', $builder->value()[1]['text']);
        $this->assertArrayHasKey('_key', $builder->value()[1]);
        $this->assertArrayHasKey('_uid', $builder->value()[1]);
    }
}
