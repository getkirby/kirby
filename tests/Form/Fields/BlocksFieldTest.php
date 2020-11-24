<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;

class BlocksFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('blocks', []);

        $this->assertSame('blocks', $field->type());
        $this->assertSame('blocks', $field->name());
        $this->assertSame(null, $field->max());
        $this->assertInstanceOf('Kirby\Cms\Fieldsets', $field->fieldsets());
        $this->assertSame([], $field->value());
        $this->assertTrue($field->save());
    }

    public function testGroups()
    {
        $field = $this->field('blocks', [
            'fieldsets' => [
                'text' => [
                    'label' => 'Text',
                    'type' => 'group',
                    'fieldsets' => [
                        'text' => true,
                        'heading' => true
                    ]
                ],
                'media' => [
                    'label' => 'Media',
                    'type' => 'group',
                    'fieldsets' => [
                        'image' => true,
                        'video' => true
                    ]
                ]
            ]
        ]);

        $groups = $field->fieldsets()->groups();

        $this->assertArrayHasKey('text', $groups);
        $this->assertArrayHasKey('media', $groups);

        $this->assertSame(['text', 'heading'], $groups['text']['sets']);
        $this->assertSame(['image', 'video'], $groups['media']['sets']);
    }

    public function testMax()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'a'
                    ]
                ],
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'b'
                    ]
                ],
            ],
            'max' => 1
        ]);

        $this->assertSame(1, $field->max());
        $this->assertFalse($field->isValid());
        $this->assertSame($field->errors()['blocks'], 'You must not add more than one block');
    }

    public function testMin()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => ['text' => 'a']
                ],
            ],
            'min' => 2
        ]);

        $this->assertSame(2, $field->min());
        $this->assertFalse($field->isValid());
        $this->assertSame($field->errors()['blocks'], 'You must add at least 2 blocks');
    }

    public function testRequired()
    {
        $field = $this->field('blocks', [
            'required' => true
        ]);

        $this->assertTrue($field->required());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('blocks', [
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'A nice heading'
                    ]
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
        $field = $this->field('blocks', $props);

        $this->assertFalse($field->fields('heading')['text']['translate']);
        $this->assertFalse($field->fields('heading')['text']['disabled']);

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('blocks', $props);
        $this->assertFalse($field->fields('heading')['text']['translate']);
        $this->assertTrue($field->fields('heading')['text']['disabled']);
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
        $field = $this->field('blocks', $props);

        $this->assertFalse($field->fieldset('heading')->translate());
        $this->assertFalse($field->fieldset('heading')->disabled());

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('blocks', $props);
        $this->assertFalse($field->fieldset('heading')->translate());
        $this->assertTrue($field->fieldset('heading')->disabled());
    }
}
