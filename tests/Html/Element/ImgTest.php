<?php

namespace Kirby\Html\Element;

use Kirby\Html\TestCase;

class ImgTest extends TestCase
{

    public function testConstruct()
    {

        // default
        $img = new Img;

        $this->assertEquals('',  $img->attr('src'));
        $this->assertEquals(' ', $img->attr('alt'));
        $this->assertEquals('<img alt="">', (string)$img);

        // custom src
        $img = new Img('image.jpg');

        $this->assertEquals('image.jpg',  $img->attr('src'));
        $this->assertEquals('<img alt="" src="image.jpg">', (string)$img);

        // custom alt attribute
        $img = new Img('image.jpg', [
            'alt' => 'test'
        ]);

        $this->assertEquals('test',  $img->attr('alt'));
        $this->assertEquals('<img alt="test" src="image.jpg">', (string)$img);

    }

}
