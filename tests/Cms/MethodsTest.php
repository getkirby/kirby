<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class MethodsTest extends TestCase
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
            'fileMethods' => [
                'test' => function () {
                    return 'file method';
                }
            ],
            'filesMethods' => [
                'test' => function () {
                    return 'files method';
                }
            ],
            'siteMethods' => [
                'test' => function () {
                    return 'site method';
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

    public function testFileMethod()
    {
        $file = $this->app->file('test/test.jpg');
        $this->assertEquals('file method', $file->test());
    }

    public function testFilesMethod()
    {
        $files = $this->app->page('test')->files();
        $this->assertEquals('files method', $files->test());
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

    public function testSiteMethod()
    {
        $site = $this->app->site();
        $this->assertEquals('site method', $site->test());
    }
}
