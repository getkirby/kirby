<?php

namespace Kirby\Panel\Areas;

class SiteSearchesTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testFilesSearch(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ],
            'request' => [
                'query' => [
                    'query' => 'test'
                ]
            ]
        ]);

        $this->login();

        $results = $this->search('files')['results'];

        $this->assertCount(1, $results);

        $this->assertSame('orange-400', $results[0]['image']['color']);
        $this->assertSame('file-image', $results[0]['image']['icon']);
        $this->assertSame('test.jpg', $results[0]['text']);
        $this->assertSame('/pages/test/files/test.jpg', $results[0]['link']);
        $this->assertSame('test/test.jpg', $results[0]['info']);
    }

    public function testPageSearch(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug'    => 'test',
                        'content' => [
                            'title' => 'Test <strong>Page'
                        ]
                    ]
                ]
            ],
            'request' => [
                'query' => [
                    'query' => 'test'
                ]
            ]
        ]);

        $this->login();

        $results = $this->search('pages')['results'];

        $this->assertCount(1, $results);

        $image = [
            'back'  => 'pattern',
            'color' => 'gray-500',
            'cover' => false,
            'icon'  => 'page',
            'ratio' => '3/2'
        ];

        $this->assertSame($image, $results[0]['image']);
        $this->assertSame('Test &lt;strong&gt;Page', $results[0]['text']);
        $this->assertSame('/pages/test', $results[0]['link']);
        $this->assertSame('test', $results[0]['info']);
    }
}
