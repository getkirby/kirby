<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use PHPUnit\Framework\TestCase;

class FileBlueprintApiModelTest extends TestCase
{
    protected $api;
    protected $app;
    protected $file;

    public function attr($object, $attr)
    {
        return $this->api->resolve($object)->select($attr)->toArray()[$attr];
    }

    public function assertAttr($object, $attr, $value)
    {
        $this->assertEquals($this->attr($object, $attr), $value);
    }

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->api  = $this->app->api();
        $this->file = new File(['filename' => 'test.jpg']);
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
