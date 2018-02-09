<?php

namespace Kirby\Form;

class HiddenFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return HiddenField::class;
    }

    public function defaultProperties(): array
    {
        return ['name' => 'test'];
    }

    public function testDisabled()
    {
        $this->assertDisabledProperty();
    }

    public function testName()
    {
        $this->assertNameProperty();
    }

    public function testType()
    {
        $this->assertTypeProperty('hidden');
    }

    public function testValue()
    {
        $this->assertPropertyValue('value', 'test');
        $this->assertPropertyIsOptional('value');
    }

}
