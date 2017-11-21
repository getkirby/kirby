<?php

namespace Kirby\Html\Element;

use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{

    public function testVideo()
    {
        $embed = new Video('http://vid.eo');
        $this->assertEquals('<iframe allowfullscreen border="0" frameborder="0" height="100%" src="http://vid.eo" width="100%"></iframe>', $embed->toString());
    }

    public function testVideoWithOptions()
    {
        $embed = new Video('http://vid.eo', [
            'option1' => 'value1',
            'option2' => 'value2'
        ]);
        $this->assertEquals('<iframe allowfullscreen border="0" frameborder="0" height="100%" src="http://vid.eo?option1=value1&option2=value2" width="100%"></iframe>', $embed->toString());
    }

    public function testVideoWithOptionsAndAttributes()
    {
        $embed = new Video('http://vid.eo', [
            'option1' => 'value1',
            'option2' => 'value2'
        ], [
            'width'   => 300,
            'height'  => 500
        ]);
        $this->assertEquals('<iframe allowfullscreen border="0" frameborder="0" height="500" src="http://vid.eo?option1=value1&option2=value2" width="300"></iframe>', $embed->toString());
    }

    public function testSrc()
    {
        $embed = new Video('test');
        $this->assertEquals('http://vid.eo', $embed->src('http://vid.eo'));
    }

    public function testSrcWithOptions()
    {
        $embed = new Video('test');
        $this->assertEquals('http://vid.eo?option1=value1&option2=value2', $embed->src('http://vid.eo', [
            'option1' => 'value1',
            'option2' => 'value2'
        ]));
    }

    public function testCreate()
    {
        $this->assertInstanceOf('Kirby\Html\Element\Video', Video::create('http://vid.eo'));
        $this->assertInstanceOf('Kirby\Html\Element\Video\Youtube', Video::create('http://www.youtube.com/embed/7C9EYka6fIU'));
        $this->assertInstanceOf('Kirby\Html\Element\Video\Youtube', Video::create('http://www.youtube.com/watch?feature=player_embedded&v=7C9EYka6fIU#!'));
        $this->assertInstanceOf('Kirby\Html\Element\Video\Youtube', Video::create('http://youtu.be/7C9EYka6fIU'));
        $this->assertInstanceOf('Kirby\Html\Element\Video\Youtube', Video::create('https://www.youtube-nocookie.com/embed/d9NF2edxy-M'));
        $this->assertInstanceOf('Kirby\Html\Element\Video\Vimeo', Video::create('https://vimeo.com/170127382'));
    }
}
