<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class TelTest extends TestCase
{
    public function testAttributes()
    {
        $tag = new Tel();
        $this->assertEquals([
            'text',
            'class',
            'role',
            'title',
            'rel'
        ], $tag->attributes());
    }

    public function testParse()
    {
        $tag  = new Tel();
        $tag->parse('+49123456789');
        $this->assertEquals('<a href="tel:+49123456789">+49123456789</a>', (string)$tag);
    }
}
