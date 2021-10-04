<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class LayoutColumnMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'layoutColumnMethods' => [
                'test' => function () {
                    return 'layout column method';
                }
            ]
        ]);
    }

    public function testLayoutColumnMethod()
    {
        $column = new LayoutColumn();
        $this->assertSame('layout column method', $column->test());
    }
}
