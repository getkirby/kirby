<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PagesRoutesTest extends TestCase
{
    public function testGet()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'b'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('pages/a');

        $this->assertEquals('a', $response['data']['id']);

        $response = $app->api()->call('pages/a+b');

        $this->assertEquals('a/b', $response['data']['id']);
    }

    public function testChildren()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'parent',
                        'children' => [
                            [
                                'slug' => 'child-a'
                            ],
                            [
                                'slug' => 'child-b'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('pages/parent/children');

        $this->assertEquals('parent/child-a', $response['data'][0]['id']);
        $this->assertEquals('parent/child-b', $response['data'][1]['id']);
    }

    public function testFiles()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'c.jpg',
                            ],
                            [
                                'filename' => 'a.jpg',
                            ],
                            [
                                'filename' => 'b.jpg',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('pages/a/files');

        $this->assertCount(3, $response['data']);
        $this->assertSame('a.jpg', $response['data'][0]['filename']);
        $this->assertSame('b.jpg', $response['data'][1]['filename']);
        $this->assertSame('c.jpg', $response['data'][2]['filename']);
    }

    public function testFilesSorted()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                                'content'  => [
                                    'sort' => 2
                                ]
                            ],
                            [
                                'filename' => 'b.jpg',
                                'content'  => [
                                    'sort' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('pages/a/files');

        $this->assertEquals('b.jpg', $response['data'][0]['filename']);
        $this->assertEquals('a.jpg', $response['data'][1]['filename']);
    }

    public function testFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('pages/a/files/a.jpg');

        $this->assertEquals('a.jpg', $response['data']['filename']);
    }
}
