<?php

namespace Kirby\Form;

class UrlFieldTest extends TextFieldTest
{

    static protected $type = 'url';

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('url');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('url');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, 'https://example.com');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, 'https://example.com');
    }

    public function testPlaceholder()
    {
        return $this->assertPlaceholderProperty('https://example.com');
    }

    public function testValue()
    {
        $this->assertValueIsValid([
            'value' => 'https://getkirby.com'
        ]);

        $this->assertValueIsInvalid([
            'value' => 'getkirby.com'
        ], 'error.form.url.invalid');
    }
}
