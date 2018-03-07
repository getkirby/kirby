<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class YoutubeTest extends TestCase
{
    public function testParse()
    {
        $tag = new Youtube();
        $this->assertEquals('<iframe allowfullscreen border="0" frameborder="0" height="100%" src="//youtube.com/embed/wOwblaKmyVw" width="100%"></iframe>', $tag->parse('https://www.youtube.com/watch?v=wOwblaKmyVw'));
    }
}
