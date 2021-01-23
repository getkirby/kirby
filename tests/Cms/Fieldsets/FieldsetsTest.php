<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FieldsetsTest extends TestCase
{
    public function testExtendGroups()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'blocks/testgroup' => [
                    'name' => 'Text',
                    'type' => 'group',
                    'fieldsets' => [
                        'heading',
                        'text'
                    ]
                ]
            ]
        ]);

        $fieldsets = Fieldsets::factory([
            'test' => [
                'extends' => 'blocks/testgroup'
            ]
        ]);

        $this->assertCount(2, $fieldsets);
        $this->assertSame('heading', $fieldsets->first()->type());
        $this->assertSame('text', $fieldsets->last()->type());

        $this->assertCount(1, $fieldsets->groups());
        $this->assertSame(['heading', 'text'], $fieldsets->groups()['test']['sets']);
    }

    public function testExtendsTabsOverwrite()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'blocks/foo' => [
                    'name' => 'Text',
                    'tabs' => [
                        'content' => [
                            'fields' => [
                                'text' => ['type' => 'textarea'],
                            ]
                        ],
                        'seo' => [
                            'fields' => [
                                'metaTitle' => ['type' => 'text'],
                                'metaDescription' => ['type' => 'text']
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $fieldsets = Fieldsets::factory([
            'bar' => [
                'extends' => 'blocks/foo',
                'tabs' => [
                    'seo' => false
                ]
            ]
        ]);

        $fieldset = $fieldsets->first();

        $this->assertIsArray($fieldset->tabs());
        $this->assertArrayHasKey('content', $fieldset->tabs());
        $this->assertArrayNotHasKey('seo', $fieldset->tabs());
    }
}
