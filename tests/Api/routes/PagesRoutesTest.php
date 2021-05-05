<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class PagesRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testGet()
    {
        $app = $this->app->clone([
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
        $app = $this->app->clone([
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

    public function testChildrenWithStatusFilter()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'parent',
                        'children' => [
                            [
                                'slug' => 'child-a',
                                'num'  => 1
                            ],
                            [
                                'slug' => 'child-b'
                            ]
                        ],
                        'drafts' => [
                            [
                                'slug' => 'draft-a'
                            ]
                        ]

                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        // all
        $response = $app->api()->call('pages/parent/children', 'GET', [
            'query' => ['status' => 'all']
        ]);

        $this->assertCount(3, $response['data']);
        $this->assertEquals('parent/child-a', $response['data'][0]['id']);
        $this->assertEquals('parent/child-b', $response['data'][1]['id']);
        $this->assertEquals('parent/draft-a', $response['data'][2]['id']);

        // published
        $response = $app->api()->call('pages/parent/children', 'GET', [
            'query' => ['status' => 'published']
        ]);

        $this->assertCount(2, $response['data']);
        $this->assertEquals('parent/child-a', $response['data'][0]['id']);
        $this->assertEquals('parent/child-b', $response['data'][1]['id']);

        // listed
        $response = $app->api()->call('pages/parent/children', 'GET', [
            'query' => ['status' => 'listed']
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('parent/child-a', $response['data'][0]['id']);

        // unlisted
        $response = $app->api()->call('pages/parent/children', 'GET', [
            'query' => ['status' => 'unlisted']
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('parent/child-b', $response['data'][0]['id']);

        // drafts
        $response = $app->api()->call('pages/parent/children', 'GET', [
            'query' => ['status' => 'drafts']
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertEquals('parent/draft-a', $response['data'][0]['id']);
    }

    public function testFiles()
    {
        $app = $this->app->clone([
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
        $app = $this->app->clone([
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
        $app = $this->app->clone([
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
