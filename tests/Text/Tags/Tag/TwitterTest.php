<?php

namespace Kirby\Text\Tags\Tag;

use PHPUnit\Framework\TestCase;

class TwitterTest extends TestCase
{
    public function testParse()
    {
        $tag = new Twitter();
        $tag->parse('getkirby');
        $this->assertEquals('<a href="https://twitter.com/getkirby">@getkirby</a>', (string)$tag);
    }
}
