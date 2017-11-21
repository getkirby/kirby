<?php

namespace Kirby\Toolkit\Url;

class HostTest extends TestCase
{

    public function testHost()
    {
        $this->assertEquals('www.youtube.com', Host::get());
    }
}
