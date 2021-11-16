<?php

namespace Kirby\Panel\Areas;

class SiteDropdownsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testChangesDropdown(): void
    {
        $this->app([
            'request' => [
                'body' => [
                    'ids' => [
                        'site',
                        'pages/test'
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ],
                'content' => [
                    'title' => 'Test site'
                ]
            ]
        ]);

        $this->login();

        $options = $this->dropdown('changes')['options'];
        $expected = [
            [
                'icon' => 'home',
                'text' => 'Test site',
                'link' => '/panel/site'
            ],
            [
                'icon' => 'page',
                'text' => 'test',
                'link' => '/panel/pages/test'
            ]
        ];

        $this->assertEquals($expected, $options);
    }

    public function testPageDropdown(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $options = $this->dropdown('pages/test')['options'];

        $title = $options[0];
        $this->assertSame([
            'url'   => '/pages/test/changeTitle',
            'query' => [
                'select' => 'title'
            ]
        ], $title['dialog']);
        $this->assertSame('Rename', $title['text']);

        $duplicate = $options[1];
        $this->assertSame('/pages/test/duplicate', $duplicate['dialog']);
        $this->assertSame('Duplicate', $duplicate['text']);

        $this->assertSame('-', $options[2]);

        $slug = $options[3];
        $this->assertSame([
            'url'   => '/pages/test/changeTitle',
            'query' => [
                'select' => 'slug'
            ]
        ], $slug['dialog']);
        $this->assertSame('Change URL', $slug['text']);

        $status = $options[4];
        $this->assertSame('/pages/test/changeStatus', $status['dialog']);
        $this->assertSame('Change status', $status['text']);

        $position = $options[5];
        $this->assertSame('/pages/test/changeSort', $position['dialog']);
        $this->assertSame('Change position', $position['text']);

        $template = $options[6];
        $this->assertSame('/pages/test/changeTemplate', $template['dialog']);
        $this->assertSame('Change template', $template['text']);

        $this->assertSame('-', $options[7]);

        $delete = $options[8];
        $this->assertSame('/pages/test/delete', $delete['dialog']);
        $this->assertSame('Delete', $delete['text']);
    }

    public function testPageDropdownInListView(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'request' => [
                'query' => [
                    'view' => 'list'
                ]
            ]
        ]);

        $this->login();

        $options = $this->dropdown('pages/test')['options'];

        $preview = $options[0];

        $this->assertSame('/test', $preview['link']);
        $this->assertSame('_blank', $preview['target']);
        $this->assertSame('Open', $preview['text']);
    }
}
