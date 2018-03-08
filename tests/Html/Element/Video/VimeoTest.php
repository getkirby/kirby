<?php

namespace Kirby\Html\Element\Video;

use PHPUnit\Framework\TestCase;

class VimeoTest extends TestCase
{

    public function testSrc()
    {
        $embed = new Vimeo('https://vimeo.com/170127382');
        $this->assertEquals('https://player.vimeo.com/video/170127382', $embed->src('https://vimeo.com/170127382'));
    }

    public function testSrcWithOptions()
    {
        $embed = new Vimeo('https://vimeo.com/170127382');
        $src = 'https://player.vimeo.com/video/170127382?option1=value1';
        $this->assertEquals($src, $embed->src('https://vimeo.com/170127382', ['option1' => 'value1']));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid Vimeo source
     */
    public function testInvalidSrc()
    {
        $embed = new Vimeo('test');
    }
}
