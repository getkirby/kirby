<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element\Img;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function _image()
    {
        $tag  = new Image();
        $tag->parse('test.jpg', [
            'link'   => 'http://getkirby.com/test.jpg',
            'alt'    => 'Kirby CMS',
            'width'  => 100,
            'height' => 100
        ]);
        return $tag;
    }

    public function testAttributes()
    {
        $tag = $this->_image();
        $this->assertEquals([
            'link',
            'alt',
            'width',
            'height'
        ], $tag->attributes());
    }


    public function testParse()
    {
        $tag = $this->_image();
        $this->assertEquals('http://getkirby.com/test.jpg', $tag->attr('link'));
        $this->assertEquals('Kirby CMS', $tag->attr('alt'));
        $this->assertEquals(100, $tag->attr('width'));
        $this->assertEquals(100, $tag->attr('height'));
    }

    public function testHtml()
    {
        $tag = $this->_image();
        $this->assertEquals('<a href="http://getkirby.com/test.jpg"><img alt="Kirby CMS" height="100" src="test.jpg" width="100"></a>', (string)$tag);
    }

    public function testHtmlWithoutLink()
    {
        $tag = new Image();
        $this->assertEquals('<img src="test.jpg">', $tag->parse('test.jpg'));
    }
}
