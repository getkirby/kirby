<?php

namespace Kirby\Form;

class HeadlineFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return HeadlineField::class;
    }

    public function testLabel()
    {
        $this->assertLabelProperty();
    }

    public function testName()
    {
        $this->assertNameProperty('headline');
    }

    public function testNumbered()
    {
        $this->assertPropertyDefault('numbered', true);
        $this->assertPropertyIsBool('numbered');
        $this->assertPropertyIsOptional('numbered');
    }

    public function testType()
    {
        $this->assertTypeProperty('headline');
    }

}
