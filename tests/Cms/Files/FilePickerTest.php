<?php

namespace Kirby\Cms;

use Kirby\Form\Field;
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
                    [
                        'filename' => 'a.jpg',
                        'template' => 'image'
                    ],
                    [
                        'filename' => 'b.jpg',
                        'template' => 'image'
                    ],
                    [
                        'filename' => 'c.jpg',
                        'template' => 'cover'
                    ],
                    [
                        'filename' => 'd.jpg',
                        'template' => 'other'
                    ]
                ]
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testDefaults()
    {
        $picker = new FilePicker();

        $this->assertCount(4, $picker->items());
    }

    public function testQuery()
    {
        $picker = new FilePicker([
            'query' => 'site.files.offset(1)'
        ]);

        $this->assertCount(3, $picker->items());
    }

    public function testTemplate()
    {
        $field = new Field('files', [
            'model' => $this->app->site(),
            'template' => 'cover'
        ]);

        $picker = new FilePicker([
            'query' => $field->query()
        ]);

        $this->assertCount(1, $picker->items());
    }

    public function testTemplates()
    {
        $field = new Field('files', [
            'model' => $this->app->site(),
            'template' => [
                'cover',
                'image'
            ]
        ]);

        $picker = new FilePicker([
            'query' => $field->query()
        ]);

        $this->assertCount(3, $picker->items());
    }
}
