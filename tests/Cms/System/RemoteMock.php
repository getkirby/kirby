<?php

namespace Kirby\Cms\System;

use Kirby\Http\Remote;

class RemoteMock extends Remote
{
    public static $mockContent = '';
    public static $mockCode = 200;

    public function fetch()
    {
        $this->content = static::$mockContent;
        $this->info    = ['http_code' => static::$mockCode];
    }
}
