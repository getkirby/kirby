<?php

namespace Kirby\Toolkit\Url;

class ParamsTest extends TestCase
{

    public function testGet()
    {
        $this->assertEquals([], Params::get());

        $this->assertEquals(['hello' => 'kitty'], Params::get($this->_docs . 'hello;kitty'));
    }

    public function testStrip()
    {
        $this->assertEquals('https://www.youtube.com/watch/?v=9q_aXttJduk', Params::strip());
        $this->assertEquals('http://getkirby.com#foo', Params::strip('http://getkirby.com/hello;kitty/#foo'));
    }

    public function testToString()
    {
        $this->assertEquals('', Params::toString());
        $this->assertEquals('bastian;allgeier/nico;hoffmann', Params::toString(['bastian' => 'allgeier', 'nico' => 'hoffmann']));
    }

    public function testSeparator()
    {
        $this->assertEquals(';', Params::separator());
    }
}
