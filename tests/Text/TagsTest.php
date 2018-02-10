<?php

namespace Kirby\Text;

use PHPUnit\Framework\TestCase;

class TagsTest extends TestCase
{
    public function tags()
    {
        return new Tags();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Tags must be a subclass of Kirby\Text\Tags\Tag
     */
    public function testSetInvalid()
    {
        new Tags([
            'test' => 'Invalid'
        ]);
    }

    public function testParse()
    {
        $tags = $this->tags();
        $this->assertEquals(date('Y'), $tags->parse('(date: year)'));
    }

    public function testParseWithInvalidTag()
    {
        $tags = $this->tags();
        $this->assertEquals('(invalid: one)', $tags->parse('(invalid: one)'));
    }

    public function testTagWithString()
    {
        $tags = $this->tags();
        $this->assertEquals(date('Y'), $tags->tag('(date: year)'));
    }

    public function testTagWithArray()
    {
        $tags = $this->tags();
        $this->assertEquals(date('Y'), $tags->tag(['date' => 'year']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid tag input
     */
    public function testTagWithInteger()
    {
        $tags = $this->tags();
        $tags->tag(5);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid tag input
     */
    public function testTagWithNull()
    {
        $tags = $this->tags();
        $tags->tag(null);
    }

    public function testTagFromString()
    {
        $tags = $this->tags();
        $this->assertEquals(date('Y'), $tags->tagFromString('(date: year)'));
    }

    public function testTagFromArray()
    {
        $tags = $this->tags();
        $this->assertEquals('<a href="https://google.com">Google</a>', $tags->tagFromArray(['link' => 'https://google.com', 'text' => 'Google']));
    }
}
