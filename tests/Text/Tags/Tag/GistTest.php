<?php

namespace Kirby\Text\Tags\Tag;

use PHPUnit\Framework\TestCase;

class GistTest extends TestCase
{
    public function testParse()
    {
        $tag = new Gist();
        $url = 'https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144';
        $this->assertEquals('<script src="' . $url . '.js"></script>', $tag->parse($url));
    }
}
