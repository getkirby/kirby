<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\File as PageFile;
use Kirby\Cms\Files as PageFiles;
use Kirby\Cms\TestCase;

class ImageTest extends TestCase
{

    public function setUp()
    {
        new App([
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);
    }

    public function testAttributes()
    {
        $tag = new Image();

        $this->assertContains('caption', $tag->attributes());
        $this->assertContains('class', $tag->attributes());
    }

    public function dataProvider()
    {
        return [
            [
                'name'     => 'test.jpg',
                'props'    => ['class' => 'test'],
                'expected' => '<figure class="test"><img alt="" src="https://getkirby.com/test.jpg"></figure>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['caption' => 'test'],
                'expected' => '<figure><img alt="" src="https://getkirby.com/test.jpg"><figcaption>test</figcaption></figure>'
            ],
            [
                'name'     => 'test.jpg',
                'props'    => ['link' => 'self'],
                'expected' => '<figure><a href="https://getkirby.com/test.jpg"><img alt="" src="https://getkirby.com/test.jpg"></a></figure>'
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

    public function testPageDependency()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => [
                'test' => '(image: test.jpg)'
            ]
        ]);

        // TODO: refactor this. Should only be arrays
        $files = new PageFiles([
            new PageFile([
                'parent'   => $page,
                'filename' => 'test.jpg'
            ])
        ], $page);

        $page = $page->clone(['files' => $files]);

        $expected = '<figure><img alt="" src="https://getkirby.com/media/pages/test/test.jpg"></figure>';

        $this->assertEquals($expected, $page->test()->kirbytags()->value());
    }

}
