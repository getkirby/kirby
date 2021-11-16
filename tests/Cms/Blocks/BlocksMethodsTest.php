<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as TestCase;

class BlocksMethodsTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'blocksMethods' => [
                'test' => function () {
                    return 'blocks method';
                }
            ]
        ]);
    }

    public function testBlocksMethod()
    {
        $input = [
            ['type' => 'heading']
        ];

        $blocks = Blocks::factory($input);
        $this->assertSame('blocks method', $blocks->test());
    }
}
