<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use Kirby\Form\Field;

class StructureFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = new Field('structure', [
            'fields' => []
        ]);

        $this->assertEquals('structure', $field->type());
        $this->assertEquals('structure', $field->name());
        $this->assertEquals(null, $field->limit());
        $this->assertEquals([], $field->fields());
        $this->assertEquals([], $field->value());
        $this->assertTrue($field->save());
    }

    public function testTagsFieldInStructure()
    {
        $field = new Field('structure', [
            'fields' => [
                'tags' => [
                    'label' => 'Tags',
                    'type'  => 'tags'
                ]
            ],
            'value' => [
                [
                    'tags' => 'a, b'
                ]
            ]
        ]);

        $expectedValue = [
            [
                'text' => 'a',
                'value' => 'a'
            ],
            [
                'text' => 'b',
                'value' => 'b'
            ]
        ];

        $this->assertEquals($expectedValue, $field->value()[0]['tags']);

        $expected = [
            [
                'tags' => 'a, b'
            ]
        ];

        $this->assertEquals($expected, $field->data());
    }

    public function testLowerCaseColumnsNames()
    {
        $field = new Field('structure', [
            'columns' => [
                'camelCase' => true
            ],
            'fields' => [
                'camelCase' => [
                    'type' => 'text'
                ]
            ],
        ]);

        $this->assertEquals(['camelcase'], array_keys($field->columns()));
    }

    public function testMin()
    {
        $field = new Field('structure', [
            'fields' => [
                'title' => [
                    'type' => 'text'
                ]
            ],
            'value' => [
                ['title' => 'a'],
            ],
            'min' => 2
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(2, $field->min());
        $this->assertTrue($field->required());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = new Field('structure', [
            'fields' => [
                'title' => [
                    'type' => 'text'
                ]
            ],
            'value' => [
                ['title' => 'a'],
                ['title' => 'b'],
            ],
            'max' => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(1, $field->max());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testNestedStructures()
    {
        $field = new Field('structure', [
            'model'  => 'test',
            'name'   => 'mothers',
            'fields' => [
                'name' => [
                    'type' => 'text',
                ],
                'children' => [
                    'type' => 'structure',
                    'fields' => [
                        'name' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ],
            'value' => $value = [
                [
                    'name' => 'Marge',
                    'children' => [
                        [
                            'name' => 'Lisa',
                        ],
                        [
                            'name' => 'Maggie',
                        ],
                        [
                            'name' => 'Bart',
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals($value, $field->value());
        $this->assertEquals($value, $field->data());

        // empty mother form
        $motherForm = $field->form();

        $expected = [
            'name'     => null,
            'children' => []
        ];

        $this->assertEquals($expected, $motherForm->data());

        // filled mother form
        $motherForm = $field->form($value[0]);
        $expected   = $value[0];

        $this->assertEquals($expected, $motherForm->data());

        $childrenField = $motherForm->fields()->children();

        $this->assertEquals('structure', $childrenField->type());
        $this->assertEquals('test', $childrenField->model());

        // empty children form
        $childrenForm = $childrenField->form();

        $this->assertEquals(['name' => null], $childrenForm->data());

        // filled children form
        $childrenForm = $childrenField->form([
            'name' => 'Test'
        ]);

        $this->assertEquals(['name' => 'Test'], $childrenForm->data());

        // children name field
        $childrenNameField = $childrenField->form()->fields()->name();

        $this->assertEquals('text', $childrenNameField->type());
        $this->assertEquals('test', $childrenNameField->model());
        $this->assertEquals(null, $childrenNameField->data());
    }

    public function testFloatsWithNonUsLocale()
    {
        $field = new Field('structure', [
            'fields' => [
                'number' => [
                    'type' => 'number'
                ]
            ],
            'value' => [
                [
                    'number' => 3.2
                ]
            ]
        ]);

        $this->assertTrue(is_float($field->data()[0]['number']));
    }

    public function testEmpty()
    {
        $field = new Field('structure', [
            'fields' => [],
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testTranslatedEmpty()
    {
        $field = new Field('structure', [
            'fields' => [],
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testTranslate()
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

        $field = new Field('structure', [
            'fields' => [
                'a' => [
                    'type' => 'text'
                ],
                'b' => [
                    'type' => 'text',
                    'translate' => false
                ]
            ]
        ]);

        $app->setCurrentLanguage('en');

        $this->assertFalse($field->form()->fields()->a()->disabled());
        $this->assertFalse($field->form()->fields()->b()->disabled());

        $app->setCurrentLanguage('de');

        $this->assertFalse($field->form()->fields()->a()->disabled());
        $this->assertTrue($field->form()->fields()->b()->disabled());
    }

    public function testDefault()
    {
        $field = new Field('structure', [
            'fields' => [
                'a' => [
                    'type' => 'text'
                ],
                'b' => [
                    'type' => 'text',
                ]
            ],
            'default' => $data = [
                [
                    'a' => 'A',
                    'b' => 'B'
                ]
            ]
        ]);

        $this->assertEquals($data, $field->data(true));
    }

    public function testRequiredProps()
    {
        $field = new Field('structure', [
            'fields' => [
                'title' => [
                    'type' => 'text'
                ]
            ],
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = new Field('structure', [
            'fields' => [
                'title' => [
                    'type' => 'text'
                ]
            ],
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = new Field('structure', [
            'fields' => [
                'title' => [
                    'type' => 'text'
                ]
            ],
            'value' => [
                ['title' => 'a'],
            ],
            'required' => true
        ]);

        $this->assertTrue($field->isValid());
    }
}
