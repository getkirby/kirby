<?php

namespace Kirby\Toolkit\Url;

class HashTest extends TestCase
{

    public function testGet()
    {
        $this->assertEquals(null, Hash::get());
        $this->assertEquals('foo', Hash::get($this->_docs . '#foo'));
    }

    public function testStrip()
    {
        $this->assertEquals('https://www.youtube.com/watch/?v=9q_aXttJduk', Hash::strip());
        $this->assertEquals('http://getkirby.com/docs', Hash::strip('http://getkirby.com/docs/#foo'));
    }
}
