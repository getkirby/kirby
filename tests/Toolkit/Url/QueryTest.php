<?php

namespace Kirby\Toolkit\Url;

class QueryTest extends TestCase
{

    public function testGet()
    {
        $this->assertEquals(['v' => '9q_aXttJduk'], Query::get());

        $this->assertEquals(['hello' => 'kitty'], Query::get($this->_docs . '?hello=kitty'));
        $this->assertEquals([], Query::get($this->_docs));
    }

    public function testIn()
    {
        $this->assertTrue(Query::in());
        $this->assertTrue(Query::in($this->_docs . '?hello=kitty'));
        $this->assertFalse(Query::in($this->_docs));
    }

    public function testToString()
    {
        $this->assertEquals('v=9q_aXttJduk', Query::toString());
        $this->assertEquals('bastian=allgeier&nico=hoffmann', Query::toString(['bastian' => 'allgeier', 'nico' => 'hoffmann']));
    }

    public function testStrip()
    {
        $this->assertEquals('https://www.youtube.com/watch', Query::strip());
        $this->assertEquals('http://getkirby.com#foo', Query::strip('http://getkirby.com/?hello=kitty#foo'));
    }
}
