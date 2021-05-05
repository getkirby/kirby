<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\PageBlueprint;
use PHPUnit\Framework\TestCase;

class PageBlueprintApiModelTest extends TestCase
{
    protected $api;
    protected $app;

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
        $this->page = new Page(['slug' => 'test']);
    }

    public function testName()
    {
        $blueprint = new PageBlueprint([
            'name'  => 'test',
            'model' => $this->page
        ]);

        $this->assertAttr($blueprint, 'name', 'test');
    }

    public function testNum()
    {
        $blueprint = new PageBlueprint([
            'name'  => 'test',
            'model' => $this->page,
            'num'   => '{{ page.year }}'
        ]);

        $this->assertAttr($blueprint, 'num', '{{ page.year }}');
    }

    public function testOptions()
    {
        $blueprint = new PageBlueprint([
            'name'  => 'test',
            'model' => $this->page
        ]);

        $options = $this->attr($blueprint, 'options');

        $this->assertArrayHasKey('changeSlug', $options);
        $this->assertArrayHasKey('changeStatus', $options);
        $this->assertArrayHasKey('changeTemplate', $options);
        $this->assertArrayHasKey('changeTitle', $options);
        $this->assertArrayHasKey('create', $options);
        $this->assertArrayHasKey('delete', $options);
        $this->assertArrayHasKey('read', $options);
        $this->assertArrayHasKey('preview', $options);
        $this->assertArrayHasKey('sort', $options);
        $this->assertArrayHasKey('update', $options);
    }

    public function testPreview()
    {
        $blueprint = new PageBlueprint([
            'name'    => 'test',
            'model'   => $this->page,
            'options' => [
                'preview' => 'test'
            ]
        ]);

        $this->assertAttr($blueprint, 'preview', 'test');
    }

    public function testStatus()
    {
        $blueprint = new PageBlueprint([
            'name'    => 'test',
            'model'   => $this->page,
            'status'  => $status = [
                'draft' => [
                    'label' => 'Test',
                    'text'  => 'Test'
                ],
            ]
        ]);

        $this->assertAttr($blueprint, 'status', $status);
    }

    public function testTabs()
    {
        $blueprint = new PageBlueprint([
            'name'  => 'test',
            'model' => $this->page
        ]);

        $this->assertAttr($blueprint, 'tabs', []);
    }

    public function testTitle()
    {
        $blueprint = new PageBlueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => $this->page
        ]);

        $this->assertAttr($blueprint, 'title', 'Test');
    }
}
