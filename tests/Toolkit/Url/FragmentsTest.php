<?php

namespace Kirby\Toolkit\Url;

class FragmentsTest extends TestCase
{

    public function testGet()
    {
        $this->assertEquals(['watch'], Fragments::get());

        $this->assertEquals(['docs', 'cheatsheet'], Fragments::get($this->_docs . 'cheatsheet'));
    }

    public function testStrip()
    {
        $this->assertEquals('https://www.youtube.com/?v=9q_aXttJduk', Fragments::strip());
        $this->assertEquals('http://getkirby.com#foo', Fragments::strip('http://getkirby.com/docs/cheatsheet#foo'));
    }
}
