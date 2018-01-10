<?php

namespace Kirby\Cms\FieldTest;
use Kirby\Cms\FieldTestCase;

class TelFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'tel';
    }

    public function testDefaultName()
    {
        $this->assertEquals('phone', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Phone', $this->field()->label());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('phone', $this->field()->icon());
    }

}
