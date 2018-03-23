<?php

namespace Kirby\Form;

class CheckboxFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return CheckboxField::class;
    }

    public function defaultProperties(): array
    {
        return [
            'name' => 'test'
        ];
    }

    public function testHelp()
    {
        $this->assertHelpProperty();
    }

    public function testIcon()
    {
        $this->assertIconProperty();
    }

    public function testLabel()
    {
        $this->assertLabelProperty();
    }

    public function testName()
    {
        $this->assertNameProperty();
    }

    public function testRequired()
    {
        $this->assertRequiredProperty();
    }

    public function testType()
    {
        $this->assertTypeProperty('checkbox');
    }

    public function testValue()
    {
        $this->assertValueIsBool();
    }

}
