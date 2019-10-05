<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class SiteRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'content' => [
                    'title' => 'Test Site'
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testGet()
    {
        $response = $this->app->api()->call('site', 'GET');

        $this->assertEquals('Test Site', $response['data']['title']);
    }

    public function testChildren()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                    ],
                    [
                        'slug' => 'b',
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/children', 'GET');

        $this->assertCount(2, $response['data']);
        $this->assertEquals('a', $response['data'][0]['id']);
        $this->assertEquals('b', $response['data'][1]['id']);
    }

    public function testFiles()
    {
        $app = $this->app->clone([
            'site' => [
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
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/files');

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
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/files');

        $this->assertEquals('b.jpg', $response['data'][0]['filename']);
        $this->assertEquals('a.jpg', $response['data'][1]['filename']);
    }

    public function testFile()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    [
                        'filename' => 'a.jpg',
                    ],
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/files/a.jpg');

        $this->assertEquals('a.jpg', $response['data']['filename']);
    }

    public function testFind()
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
                                'slug' => 'aa'
                            ],
                            [
                                'slug' => 'ab'
                            ]
                        ],
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ],
        ]);

        $app->impersonate('kirby');

        // find single
        $result = $app->api()->call('site/find', 'POST', [
            'body' => [
                'a',
            ]
        ]);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('a', $result['data'][0]['id']);

        // find multiple
        $result = $app->api()->call('site/find', 'POST', [
            'body' => [
                'a',
                'a/aa',
                'b'
            ]
        ]);

        $this->assertCount(3, $result['data']);
        $this->assertEquals('a', $result['data'][0]['id']);
        $this->assertEquals('a/aa', $result['data'][1]['id']);
        $this->assertEquals('b', $result['data'][2]['id']);
    }


    public function testSearchWithGetRequest()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'parent',
                        'content' => [
                            'title' => 'Projects'
                        ],
                        'children' => [
                            [
                                'slug' => 'child',
                                'content' => [
                                    'title' => 'Photography'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/search', 'GET', [
            'query' => [
                'q' => 'Photo'
            ]
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('parent/child', $response['data'][0]['id']);
    }

    public function testSearchWithPostRequest()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'parent',
                        'content' => [
                            'title' => 'Projects'
                        ],
                        'children' => [
                            [
                                'slug' => 'child',
                                'content' => [
                                    'title' => 'Photography'
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $response = $app->api()->call('site/search', 'POST', [
            'body' => [
                'search' => 'Photo'
            ]
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('parent/child', $response['data'][0]['id']);
    }
}
