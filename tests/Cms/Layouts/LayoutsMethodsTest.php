<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class LayoutsMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'layoutsMethods' => [
                'test' => function () {
                    return 'layouts method';
                }
            ]
        ]);
    }

    public function testLayoutsMethod()
    {
        $layouts = Layouts::factory();
        $this->assertSame('layouts method', $layouts->test());
    }
}
