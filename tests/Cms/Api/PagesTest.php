<?php

namespace Kirby\Cms\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class PagesTest extends TestCase
{
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

        $this->assertEquals('a.jpg', $response['data'][0]['filename']);
        $this->assertEquals('b.jpg', $response['data'][1]['filename']);
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
}
