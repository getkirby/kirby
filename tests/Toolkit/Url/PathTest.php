<?php

namespace Kirby\Toolkit\Url;

class PathTest extends TestCase
{

    public function testGet()
    {
        $this->assertEquals('watch', Path::get());
        $this->assertEquals('docs/cheatsheet', Path::get($this->_docs . 'cheatsheet'));

        // relative path
        $this->assertEquals('docs/cheatsheet', Path::get('docs/cheatsheet'));
    }

    public function testStrip()
    {
        $this->assertEquals('https://www.youtube.com/?v=9q_aXttJduk', Path::strip());
        $this->assertEquals('http://getkirby.com/#foo', Path::strip('http://getkirby.com/docs/cheatsheet#foo'));
    }
}
