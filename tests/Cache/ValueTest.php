<?php

namespace Kirby\Cache;

use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    public function testSetup()
    {
        $value = new Value('foo');
        $this->assertEquals('foo', $value->value());
        $this->assertEquals(time(), $value->created());
        $this->assertEquals(time() + (2628000 * 60), $value->expires());
    }

    public function testSetupCustomDuration()
    {
        $value = new Value('foo', 100);
        $this->assertEquals(time() + (100 * 60), $value->expires());
    }
}
