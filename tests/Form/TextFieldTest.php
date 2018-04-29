<?php

namespace Kirby\Form;

class TextFieldTest extends FieldTestCase
{

    static protected $type = 'text';

    public function testAutocomplete()
    {
        $this->assertAutocompleteProperty();
    }

    public function testConverter()
    {
        $this->assertConverterProperty();
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

    public function testPlaceholder()
    {
        $this->assertPlaceholderProperty();
    }

    public function testRequired()
    {
        $this->assertRequiredProperty();
    }
}
