<?php

namespace Kirby\Form;

class UrlFieldTest extends TextFieldTest
{

    public function className(): string
    {
        return UrlField::class;
    }

    public function defaultName()
    {
        return 'url';
    }

    public function testAutocomplete()
    {
        return $this->assertAutocompleteProperty('url');
    }

    public function testIcon()
    {
        return $this->assertIconProperty('url');
    }

    public function testLabel()
    {
        return $this->assertLabelProperty('Url');
    }

    public function testMaxLength()
    {
        $this->assertMaxLengthProperty(null, 'https://example.com');
    }

    public function testMinLength()
    {
        $this->assertMinLengthProperty(null, 'https://example.com');
    }

    public function testName()
    {
        $this->assertNameProperty('url');
    }

    public function testPlaceholder()
    {
        return $this->assertPlaceholderProperty('https://example.com');
    }

    public function testType()
    {
        $this->assertTypeProperty('url');
    }

    public function testValue()
    {
        $this->assertValueIsValid([
            'value' => 'https://getkirby.com'
        ]);

        $this->assertValueIsInvalid([
            'value' => 'getkirby.com'
        ], 'url');
    }

}
