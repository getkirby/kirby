<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class EmailFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'email';
    }

    public function testDefaultAutocomplete()
    {
        $this->assertEquals('email', $this->field()->autocomplete());
    }

    public function testDefaultName()
    {
        $this->assertEquals('email', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Email', $this->field()->label());
    }

    public function testDefaultPlaceholder()
    {
        $this->assertEquals('mail@example.com', $this->field()->placeholder());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('email', $this->field()->icon());
    }

}
