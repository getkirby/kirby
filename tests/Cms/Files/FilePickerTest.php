<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FilePickerTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'a.jpg'],
                    ['filename' => 'b.jpg'],
                    ['filename' => 'c.jpg']
                ],
                'children' => [
                    [
                        'slug'  => 'test',
                        'files' => [
                            ['filename' => 'd.jpg'],
                            ['filename' => 'e.jpg']
                        ]
                    ]
                ],
            ],
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'f.jpg']
                    ]
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testDefaults()
    {
        $picker = new FilePicker();

        $this->assertCount(3, $picker->items());
    }

    public function testQuery()
    {
        $picker = new FilePicker([
            'query' => 'site.files.offset(1)'
        ]);

        $this->assertCount(2, $picker->items());
    }

    public function testQuerySite()
    {
        $picker = new FilePicker([
            'query' => 'site'
        ]);

        $this->assertCount(3, $picker->items());
    }

    public function testQueryPage()
    {
        $picker = new FilePicker([
            'query' => 'kirby.page("test")'
        ]);

        $this->assertCount(2, $picker->items());
    }

    public function testQueryUser()
    {
        $picker = new FilePicker([
            'query' => 'kirby.user("test")'
        ]);

        $this->assertCount(1, $picker->items());
    }

    public function testQueryFile()
    {
        $picker = new FilePicker([
            'model' => $this->app->site()->files()->first()
        ]);

        $this->assertCount(3, $picker->items());
    }

    public function testQueryInvalid()
    {
        $picker = new FilePicker([
            'query' => 'site.pages'
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Your query must return a set of files');

        $picker->items();
    }
}
