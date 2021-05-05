<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use PHPUnit\Framework\TestCase;

class SiteApiModelTest extends TestCase
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

        $this->api = $this->app->api();
    }

    public function testBlueprint()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'site' => [
                    'title' => 'Test'
                ]
            ],
        ]);

        $site      = $this->app->site();
        $blueprint = $this->attr($site, 'blueprint');

        $this->assertEquals('Test', $blueprint['title']);
    }

    public function testChildren()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b'],
            ]
        ]);

        $children = $this->attr($site, 'children');

        $this->assertEquals('a', $children[0]['id']);
        $this->assertEquals('b', $children[1]['id']);
    }

    public function testContent()
    {
        $site = new Site([
            'content' => $content = [
                ['a' => 'A'],
                ['b' => 'B'],
            ]
        ]);

        $this->assertAttr($site, 'content', $content);
    }

    public function testDrafts()
    {
        $site = new Site([
            'drafts' => [
                ['slug' => 'a'],
                ['slug' => 'b'],
            ]
        ]);

        $drafts = $this->attr($site, 'drafts');

        $this->assertEquals('a', $drafts[0]['id']);
        $this->assertEquals('b', $drafts[1]['id']);
    }

    public function testFiles()
    {
        $site = new Site([
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
            ]
        ]);

        $files = $this->attr($site, 'files');

        $this->assertEquals('a.jpg', $files[0]['filename']);
        $this->assertEquals('b.jpg', $files[1]['filename']);
    }

    public function testTitle()
    {
        $site = new Site([
            'content' => [
                'title' => 'Test',
            ]
        ]);

        $this->assertAttr($site, 'title', 'Test');
    }

    public function testUrl()
    {
        $site = new Site([
            'url' => 'https://getkirby.com'
        ]);

        $this->assertAttr($site, 'url', 'https://getkirby.com');
    }
}
