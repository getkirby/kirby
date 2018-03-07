<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class VimeoTest extends TestCase
{
    public function testParse()
    {
        $tag = new Vimeo();
        $this->assertEquals('<iframe allowfullscreen border="0" frameborder="0" height="100%" src="//player.vimeo.com/video/94744558" width="100%"></iframe>', $tag->parse('https://vimeo.com/94744558'));
    }
}
