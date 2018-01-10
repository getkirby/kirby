<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class AuthorFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'author';
    }

    public function testDefaultName()
    {
        $this->assertEquals('author', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Author', $this->field()->label());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('user', $this->field()->icon());
    }

}
