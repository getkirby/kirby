<?php

namespace Kirby\Form;

class TelFieldTest extends TextFieldTest
{

    static protected $type = 'tel';

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('tel');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('phone');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, '1234');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, '1234');
    }
}
