<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

class ContentLocksTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function app()
    {
        return new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/ContentLocksTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'test'
                    ]
                ]
            ]
        ]);
    }

    public function setUp(): void
    {
        $this->app = $this->app();
        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testFile()
    {
        $app = $this->app;
        $page = $app->page('test');
        $this->assertTrue(Str::endsWith($app->locks()->file($page), 'content/test/.lock'));
    }

    public function testId()
    {
        $app = $this->app;
        $page = $app->page('test');
        $this->assertEquals('/test', $app->locks()->id($page));
    }

    public function testGetSet()
    {
        $app = $this->app;
        $page = $app->page('test');
        $root = $this->fixtures . '/content/test';

        // create fixtures directory
        $this->assertEquals($root . '/.lock', $app->locks()->file($page));
        Dir::make($root);

        // check if empty
        $this->assertEquals([], $app->locks()->get($page));
        $this->assertFalse(F::exists($app->locks()->file($page)));

        // set data
        $this->assertTrue($app->locks()->set($page, [
            'lock'   => ['user' => 'homer'],
            'unlock' => []
        ]));

        // check if exists
        $this->assertTrue(F::exists($app->locks()->file($page)));
        $this->assertEquals([
            'lock' => ['user' => 'homer']
        ], $app->locks()->get($page));

        // set null data
        $this->assertTrue($app->locks()->set($page, []));

        // check if empty
        $this->assertEquals([], $app->locks()->get($page));
        $this->assertFalse(F::exists($app->locks()->file($page)));
    }
}
