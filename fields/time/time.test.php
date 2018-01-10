<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class TimeFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'time';
    }

    public function testDefaultName()
    {
        $this->assertEquals('time', $this->field()->name());
    }

    public function testDefaultLabel()
    {
        $this->assertEquals('Time', $this->field()->label());
    }

    public function testDefaultIcon()
    {
        $this->assertEquals('clock', $this->field()->icon());
    }

}
