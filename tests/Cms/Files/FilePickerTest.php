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
}
