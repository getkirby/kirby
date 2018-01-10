<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class UrlFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'url';
    }

    public function testDefaultName()
    {
        $this->assertEquals('url', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Url', $this->field()->label());
    }

    public function testDefaultPlaceholder()
    {
        $this->assertEquals('https://example.com', $this->field()->placeholder());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('url', $this->field()->icon());
    }

}
