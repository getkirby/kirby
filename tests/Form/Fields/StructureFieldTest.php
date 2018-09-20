<?php

namespace Kirby\Form\Fields;

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

        $expectedYaml = Yaml::encode([
            [
                'tags' => 'a, b'
            ]
        ]);

        $this->assertEquals($expectedYaml, $field->toString());
    }

}
