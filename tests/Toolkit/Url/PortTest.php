<?php

namespace Kirby\Toolkit\Url;

class PortTest extends TestCase
{

    public function testGet()
    {
        $this->assertFalse(Port::get());
        $this->assertEquals('9090', Port::get('http://user:pw@host:9090/'));
    }

}
