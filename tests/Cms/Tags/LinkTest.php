<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\TestCase;

class LinkTest extends TestCase
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
                'name'     => 'projects',
                'props'    => [],
                'expected' => '<a href="https://getkirby.com/projects">https://getkirby.com/projects</a>'
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTag($name, $props, $expected)
    {
        $tag    = new Link();
        $result = $tag->parse($name, $props);

        $this->assertEquals($expected, $result);
    }

}
