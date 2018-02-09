<?php

namespace Kirby\Form;

class TelFieldTest extends TextFieldTest
{

    public function className(): string
    {
        return TelField::class;
    }

    public function defaultName()
    {
        return 'phone';
    }

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('tel');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('phone');
    }

    public function testLabel()
    {
        return $this->assertLabelProperty('Phone');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, '1234');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, '1234');
    }

    public function testName()
    {
        $this->assertNameProperty('phone');
    }

    public function testType()
    {
        $this->assertTypeProperty('tel');
    }

}
