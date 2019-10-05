<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class IngredientsTest extends TestCase
{
    protected $ingredients;

    public function setUp(): void
    {
        $this->ingredients = Ingredients::bake([
            'a' => 'A',
            'b' => function () {
                return 'B';
            }
        ]);
    }

    public function testGet()
    {
        $this->assertEquals('A', $this->ingredients->a);
        $this->assertEquals('B', $this->ingredients->b);
    }

    public function testCall()
    {
        $this->assertEquals('A', $this->ingredients->a());
        $this->assertEquals('B', $this->ingredients->b());
    }

    public function testToArray()
    {
        $expected = [
            'a' => 'A',
            'b' => 'B'
        ];

        $this->assertEquals($expected, $this->ingredients->toArray());
        $this->assertEquals($expected, $this->ingredients->__debugInfo());
    }
}
