<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;

class BuilderFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('builder', [
            'fieldsets' => []
        ]);

        $this->assertSame('builder', $field->type());
        $this->assertSame('builder', $field->name());
        $this->assertSame(null, $field->max());
        $this->assertTrue(is_array($field->fieldsets()));
        $this->assertSame([], $field->value());
        $this->assertTrue($field->save());
    }

    public function testMax()
    {
        $field = $this->field('builder', [
            'fieldsets' => [
                'heading' => [
                    'fields' => [
                        'text' => [
                            'type' => 'text',
                            'translate' => false,
                        ]
                    ]
                ]
            ],
            'value' => [
                [
                    '_key' => 'heading',
                    'text' => 'a'
                ],
                [
                    '_key'  => 'heading',
                    'title' => 'b'
                ],
            ],
            'max' => 1
        ]);

        $this->assertSame(1, $field->max());
        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testRequired()
    {
        $field = $this->field('builder', [
            'fieldsets' => [],
            'required' => true
        ]);

        $this->assertTrue($field->required());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('builder', [
            'fieldsets' => [],
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('builder', [
            'fieldsets' => [
                'heading' => [
                    'fields' => [
                        'text' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ],
            'value' => [
                [
                    '_key' => 'heading',
                    'text' => 'A nice heading'
                ],
            ],
            'required' => true
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testTranslateField()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code' => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $props = [
            'fieldsets' => [
                'heading' => [
                    'fields' => [
                        'text' => [
                            'type' => 'text',
                            'translate' => false,
                        ]
                    ]
                ]
            ]
        ];

        // default language
        $app->setCurrentLanguage('en');
        $field = $this->field('builder', $props);

        $this->assertFalse($field->fieldsets['heading']['tabs']['content']['fields']['text']['translate']);
        $this->assertFalse($field->fieldsets['heading']['tabs']['content']['fields']['text']['disabled']);

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('builder', $props);
        $this->assertFalse($field->fieldsets['heading']['tabs']['content']['fields']['text']['translate']);
        $this->assertTrue($field->fieldsets['heading']['tabs']['content']['fields']['text']['disabled']);
    }

    public function testTranslateFieldset()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code' => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $props = [
            'fieldsets' => [
                'heading' => [
                    'translate' => false,
                    'fields' => [
                        'text' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];

        // default language
        $app->setCurrentLanguage('en');
        $field = $this->field('builder', $props);

        $this->assertFalse($field->fieldsets['heading']['translate']);
        $this->assertFalse($field->fieldsets['heading']['disabled']);

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('builder', $props);
        $this->assertFalse($field->fieldsets['heading']['translate']);
        $this->assertTrue($field->fieldsets['heading']['disabled']);
    }
}
