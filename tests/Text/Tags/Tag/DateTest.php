<?php

namespace Kirby\Text\Tags\Tag;

use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testParseWithYear()
    {
        $tag = new Date();
        $this->assertEquals(date('Y'), $tag->parse('year'));
    }

    public function testParseWithDateParameter()
    {
        $tag = new Date();
        $this->assertEquals(date('d.m.Y'), $tag->parse('d.m.Y'));
    }
}
