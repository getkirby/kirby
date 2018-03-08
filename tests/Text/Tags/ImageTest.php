<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element\Img;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{

    public function testAttributes()
    {
        $tag = new Image();
        $this->assertEquals([
            'alt',
            'height',
            'imgClass',
            'link',
            'linkClass',
            'rel',
            'target',
            'title',
            'width',
        ], $tag->attributes());
    }

    public function dataProvider()
    {
        return [
            [
                'name'     => 'test.jpg',
                'props'    => ['alt' => 'test'],
                'expected' => '<img alt="test" src="test.jpg">'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['height' => '100%'],
                'expected' => '<img alt="" height="100%" src="test.jpg">'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['imgClass' => 'test'],
                'expected' => '<img alt="" class="test" src="test.jpg">'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['link' => '#test'],
                'expected' => '<a href="#test"><img alt="" src="test.jpg"></a>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['link' => '#test', 'linkClass' => 'test'],
                'expected' => '<a class="test" href="#test"><img alt="" src="test.jpg"></a>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['link' => '#test', 'rel' => 'me'],
                'expected' => '<a href="#test" rel="me"><img alt="" src="test.jpg"></a>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['link' => '#test','target' => '_blank'],
                'expected' => '<a href="#test" rel="noopener noreferrer" target="_blank"><img alt="" src="test.jpg"></a>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['title' => 'test'],
                'expected' => '<img alt="" src="test.jpg" title="test">'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['width' => '100%'],
                'expected' => '<img alt="" src="test.jpg" width="100%">'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTag($name, $props, $expected)
    {
        $tag    = new Image();
        $result = $tag->parse($name, $props);

        $this->assertEquals($expected, $result);
    }

}
