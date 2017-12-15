<?php

namespace Kirby\Cms;

class BlueprintFieldTest extends TestCase
{

    public function field(array $props = [])
    {
        return new BlueprintField(array_merge([
            'label' => 'Test',
            'name'  => 'test',
            'type'  => 'text'
        ], $props));
    }

    public function testDefaultId()
    {
        $this->assertEquals('test', $this->field()->id());
    }

    public function testId()
    {
        $this->assertEquals('some-id', $this->field(['id' => 'some-id'])->id());
    }

    public function testLabel()
    {
        $field = $this->field();
        $this->assertEquals('Test', $field->label());
    }

    public function testName()
    {
        $field = $this->field();
        $this->assertEquals('test', $field->name());
    }

    public function testType()
    {
        $field = $this->field();
        $this->assertEquals('text', $field->type());
    }

    public function testToArray()
    {
        $field    = $this->field();
        $expected = [
            'label' => 'Test',
            'name'  => 'test',
            'type'  => 'text',
            'id'    => 'test'
        ];

        $this->assertEquals($expected, $field->toArray());
    }

}
