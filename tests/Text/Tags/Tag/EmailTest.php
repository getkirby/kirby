<?php

namespace Kirby\Text\Tags\Tag;

use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testAttributes()
    {
        $tag = new Email();
        $this->assertEquals([
            'text',
            'class',
            'role',
            'title',
            'rel'
        ], $tag->attributes());
    }

    public function testWithoutText()
    {
        $tag  = new Email();
        $html = '<a href="mailto:bastian@getkirby.com">bastian@getkirby.com</a>';
        $this->assertEquals($html, $tag->parse('bastian@getkirby.com'));
    }

    public function testWithText()
    {
        $tag  = new Email();
        $html = '<a href="mailto:bastian@getkirby.com">Bastian</a>';
        $this->assertEquals($html, $tag->parse('bastian@getkirby.com', ['text' => 'Bastian']));
    }
}
