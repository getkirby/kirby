<?php

namespace Kirby\Form;

class EmailFieldTest extends TextFieldTest
{

    static protected $type = 'email';

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('email');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('email');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, 'mail@example.com');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, 'mail@example.com');
    }

    public function testPlaceholder()
    {
        return $this->assertPlaceholderProperty('mail@example.com');
    }

    public function testValue()
    {
        $this->assertValueIsValid([
            'value' => 'mail@getkirby.com'
        ]);

        $this->assertValueIsInvalid([
            'value' => 'mail[at]getkirby[dot]com'
        ], 'error.form.email.invalid');
    }

}
