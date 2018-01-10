<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Data\Handler\Yaml;
use Kirby\Cms\FieldTestCase;

class StructureFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'structure';
    }

    public function props(): array
    {
        return [
            'name'   => 'test',
            'fields' => [
                'title' => [
                    'label' => 'Title',
                    'type'  => 'text',
                ],
                'text' => [
                    'label' => 'Text',
                    'type'  => 'textarea',
                ]
            ]
        ];
    }

    public function data()
    {
        return [
            [
                'title' => 'Title A',
                'text'  => 'Text A'
            ],
            [
                'title' => 'Title B',
                'text'  => 'Text B'
            ],
        ];
    }

    public function testValue()
    {
        $field = $this->field([
            'value' => Yaml::encode($this->data())
        ]);

        $this->assertEquals($this->data(), $field->value());
    }

    public function testSubmit()
    {
        $field  = $this->field();
        $result = $field->submit($this->data());

        $this->assertEquals(Yaml::encode($this->data()), $result);
    }

}
