<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Cms\File as PageFile;
use Kirby\Cms\Files as PageFiles;
use Kirby\Cms\Page;
use Kirby\Cms\TestCase;

class FileTest extends TestCase
{

    public function setUp()
    {
        new App([
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);
    }

    public function dataProvider()
    {
        return [
            [
                'name'     => 'test.pdf',
                'props'    => [],
                'expected' => '<a download href="https://getkirby.com/test.pdf">test.pdf</a>'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTag($name, $props, $expected)
    {
        $tag    = new File();
        $result = $tag->parse($name, $props);

        $this->assertEquals($expected, $result);
    }

    public function testPageDependency()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'test' => '(file: test.jpg)'
            ],
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $expected = '<a download href="https://getkirby.com/media/pages/test/test.jpg">test.jpg</a>';
        $this->assertEquals($expected, $page->test()->kirbytags()->value());
    }

}
