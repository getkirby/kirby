<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class NestTest extends TestCase
{
    public function testCreateScalar()
    {
        $n = Nest::create($expected = 'a');

        $this->assertInstanceOf(Field::class, $n);
        $this->assertEquals($expected, $n);
    }

    public function testCreateObject()
    {
        $n = Nest::create($expected = [
            'a' => 'A',
            'b' => 2,
            'c' => false
        ]);

        $this->assertInstanceOf(NestObject::class, $n);
        $this->assertEquals($expected, $n->toArray());
    }

    public function testCreateCollection()
    {
        $n = Nest::create($expected = ['A', 2, false]);

        $this->assertInstanceOf(NestCollection::class, $n);
        $this->assertEquals('A', $n->first()->value());
        $this->assertEquals(false, $n->last()->value());
    }
}
