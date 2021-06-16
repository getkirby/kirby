<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class FileBlueprintApiModelTest extends ApiModelTestCase
{
    protected $file;

    public function setUp(): void
    {
        parent::setUp();

        $page = new Page([
            'slug' => 'test'
        ]);

        $this->file = new File(['filename' => 'test.jpg', 'parent' => $page]);
    }

    public function testName()
    {
        $blueprint = new FileBlueprint([
            'name'  => 'test',
            'model' => $this->file
        ]);

        $this->assertAttr($blueprint, 'name', 'test');
    }

    public function testOptions()
    {
        $blueprint = new FileBlueprint([
            'name'  => 'test',
            'model' => $this->file
        ]);

        $options = $this->attr($blueprint, 'options');

        $this->assertArrayHasKey('changeName', $options);
        $this->assertArrayHasKey('create', $options);
        $this->assertArrayHasKey('delete', $options);
        $this->assertArrayHasKey('replace', $options);
        $this->assertArrayHasKey('update', $options);
    }

    public function testTabs()
    {
        $blueprint = new FileBlueprint([
            'name'  => 'test',
            'model' => $this->file
        ]);

        $this->assertAttr($blueprint, 'tabs', []);
    }

    public function testTitle()
    {
        $blueprint = new FileBlueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => $this->file
        ]);

        $this->assertAttr($blueprint, 'title', 'Test');
    }
}
