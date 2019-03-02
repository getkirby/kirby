<?php

namespace Kirby\Cms;

class VisitorTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testInstance()
    {
        $this->assertEquals($this->app->visitor(), Visitor::instance());
    }
}
