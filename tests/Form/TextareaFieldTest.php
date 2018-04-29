<?php

namespace Kirby\Form;

class TextareaFieldTest extends FieldTestCase
{

    static protected $type = 'textarea';

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
