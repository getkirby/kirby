<?php

namespace Kirby\Html\Element;

use PHPUnit\Framework\TestCase;

class GistTest extends TestCase
{

    public function testGist()
    {
        $embed = new Gist('https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144');
        $this->assertEquals('<script src="https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144.js"></script>', $embed);
    }

    public function testGistFile()
    {
        $embed = new Gist('https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144', 'graphql.js');
        $this->assertEquals('<script src="https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144.js?file=graphql.js"></script>', $embed);
    }
}
