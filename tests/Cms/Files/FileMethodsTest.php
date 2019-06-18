<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class FileMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
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
}
