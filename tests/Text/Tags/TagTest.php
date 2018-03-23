<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class DummyTag extends Tag {}

class TagTest extends TestCase
{
    public function tag()
    {
        return new DummyTag();
    }

    public function testAttributes()
    {
        $tag = $this->tag();
        $this->assertEquals([], $tag->attributes());
    }

    public function testAttr()
    {
        $tag = $this->tag();
        $this->assertEquals('default', $tag->attr('attribute', 'default'));
        $html = $tag->parse('value', ['attribute' => 'attribute']);
        $this->assertEquals('attribute', $tag->attr('attribute', 'default'));
        $this->assertEquals('default', $tag->attr('another', 'default'));
    }

    public function testParse()
    {
        $tag = $this->tag();
        $this->assertEquals([], $tag->attributes());
    }

    public function testValue()
    {
        $tag  = $this->tag();
        $html = $tag->parse('value', ['attribute' => 'attribute']);
        $this->assertEquals('', $html);
        $this->assertEquals('value', $tag->value());
    }
}
