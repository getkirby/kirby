<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class LayoutMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'layoutMethods' => [
                'test' => function () {
                    return 'layout method';
                }
            ]
        ]);
    }

    public function testLayoutMethod()
    {
        $layout = new Layout();
        $this->assertSame('layout method', $layout->test());
    }
}
