<?php

namespace Kirby\Html\Element\Video;

use PHPUnit\Framework\TestCase;

class YoutubeTest extends TestCase
{

    public function testSrc()
    {
        $embed = new Youtube('http://www.youtube.com/embed/7C9EYka6fIU');
        $urls  = [
            'http://www.youtube.com/embed/7C9EYka6fIU',
            'http://www.youtube.com/watch?feature=player_embedded&v=7C9EYka6fIU#!',
            'http://youtu.be/7C9EYka6fIU'
        ];
        $src  = '//youtube.com/embed/7C9EYka6fIU';

        $this->assertEquals($src, $embed->src($urls[0]));
        $this->assertEquals($src, $embed->src($urls[1]));
        $this->assertEquals($src, $embed->src($urls[2]));
    }

    public function testSrcNoCookies()
    {
        $url   = 'https://www.youtube-nocookie.com/embed/d9NF2edxy-M';
        $embed = new Youtube($url);
        $src   = '//www.youtube-nocookie.com/embed/d9NF2edxy-M';

        $this->assertEquals($src, $embed->src($url));
    }

    public function testSrcWithOptions()
    {
        $embed   = new Youtube('http://www.youtube.com/embed/7C9EYka6fIU');
        $urls    = [
            'http://www.youtube.com/embed/7C9EYka6fIU',
            'http://www.youtube.com/watch?feature=player_embedded&v=7C9EYka6fIU#!',
            'http://youtu.be/7C9EYka6fIU'
        ];
        $options = ['option1' => 'value1'];
        $src     = '//youtube.com/embed/7C9EYka6fIU?option1=value1';

        $this->assertEquals($src, $embed->src($urls[0], $options));
        $this->assertEquals($src, $embed->src($urls[1], $options));
        $this->assertEquals($src, $embed->src($urls[2], $options));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid Youtube source
     */
    public function testInvalidSrc()
    {
        $embed = new Youtube('test');
    }
}
