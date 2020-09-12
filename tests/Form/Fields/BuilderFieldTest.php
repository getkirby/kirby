<?php

namespace Kirby\Form\Fields;

class BuilderFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('builder', [
            'fieldsets' => []
        ]);

        $this->assertSame('builder', $field->type());
        $this->assertSame('builder', $field->name());
        $this->assertSame(1, $field->columns());
        $this->assertSame(null, $field->max());
        $this->assertTrue(is_array($field->fieldsets()));
        $this->assertSame([], $field->value());
        $this->assertTrue($field->save());
    }

    public function testColumns()
    {
        $field = $this->field('builder', [
            'columns' => 2,
            'fieldsets' => [],
            'max' => 1
        ]);

        $this->assertSame(2, $field->columns());
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
}
