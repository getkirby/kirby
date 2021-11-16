<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;
use Kirby\Filesystem\Dir;

class FileVersionApiModelTest extends ApiModelTestCase
{
    protected $file;
    protected $parent;
    protected $fixtures;

    public function setUp(): void
    {
        parent::setUp();

        $this->parent = new Page([
            'root' => $this->fixtures = __DIR__ . '/fixtures',
            'slug' => 'test'
        ]);

        $this->file = new File([
            'filename' => 'test.jpg',
            'parent' => $this->parent,
            'content' => [
                'title' => 'Test Title'
            ]
        ]);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testExists()
    {
        $version = new FileVersion([
            'original' => $this->file,
            'root'     => $this->fixtures . '/test-version.jpg',
        ]);

        $this->assertAttr($version, 'exists', false);
    }

    public function testType()
    {
        $version = new FileVersion([
            'original' => $this->file,
            'root'     => $this->fixtures . '/test-version.jpg',
        ]);

        $this->assertAttr($version, 'type', 'image');
    }

    public function testUrl()
    {
        $version = new FileVersion([
            'original' => $this->file,
            'root'     => $this->fixtures . '/test-version.jpg',
        ]);

        $this->assertAttr($version, 'url', null);
    }
}
