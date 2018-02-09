<?php

namespace Kirby\Form;

class EmailFieldTest extends TextFieldTest
{

    public function className(): string
    {
        return EmailField::class;
    }

    public function defaultName()
    {
        return 'email';
    }

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('email');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('email');
    }

    public function testLabel()
    {
        return $this->assertLabelProperty('Email');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, 'mail@example.com');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, 'mail@example.com');
    }

    public function testName()
    {
        $this->assertNameProperty('email');
    }

    public function testPlaceholder()
    {
        return $this->assertPlaceholderProperty('mail@example.com');
    }

    public function testType()
    {
        $this->assertTypeProperty('email');
    }

    public function testValue()
    {
        $this->assertValueIsValid([
            'value' => 'mail@getkirby.com'
        ]);

        $this->assertValueIsInvalid([
            'value' => 'mail[at]getkirby[dot]com'
        ], 'email');
    }

}
