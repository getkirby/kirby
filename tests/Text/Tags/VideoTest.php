<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testVimeo()
    {
        $tag = new Video();
        $this->assertEquals('<figure><iframe allowfullscreen border="0" frameborder="0" height="100%" src="https://player.vimeo.com/video/94744558" width="100%"></iframe></figure>', $tag->parse('https://vimeo.com/94744558'));
    }

    public function testYoutube()
    {
        $tag = new Video();
        $this->assertEquals('<figure><iframe allowfullscreen border="0" frameborder="0" height="100%" src="https://youtube.com/embed/wOwblaKmyVw" width="100%"></iframe></figure>', $tag->parse('https://www.youtube.com/watch?v=wOwblaKmyVw'));
    }
}
