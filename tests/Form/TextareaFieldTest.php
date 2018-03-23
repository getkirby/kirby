<?php

namespace Kirby\Form;

class TextareaFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return TextareaField::class;
    }

    public function testConverter()
    {
        $this->assertConverterProperty();
    }

    public function testHelp()
    {
        $this->assertHelpProperty();
    }

    public function testIcon()
    {
        $this->assertIconProperty();
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty();
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty();
    }

    public function testLabel()
    {
        $this->assertLabelProperty('Text');
    }

    public function testName()
    {
        $this->assertNameProperty('text');
    }

    public function testPlaceholder()
    {
        $this->assertPlaceholderProperty();
    }

    public function testRequired()
    {
        $this->assertRequiredProperty();
    }

    public function testType()
    {
        $this->assertTypeProperty('textarea');
    }

}
