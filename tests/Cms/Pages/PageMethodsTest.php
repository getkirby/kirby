<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class PageMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'pageMethods' => [
                'test' => function () {
                    return 'page method';
                }
            ],
            'pagesMethods' => [
                'test' => function () {
                    return 'pages method';
                }
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'test',
                        'files' => [
                            [
                                'filename' => 'test.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testPageMethod()
    {
        $page = $this->app->page('test');
        $this->assertEquals('page method', $page->test());
    }

    public function testPagesMethod()
    {
        $pages = $this->app->site()->children();
        $this->assertEquals('pages method', $pages->test());
    }
}
