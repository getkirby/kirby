<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;

class STest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/STest'
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testInstance()
    {
        $this->assertEquals($this->app->session(), S::instance());
    }
}
