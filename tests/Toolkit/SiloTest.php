<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class SiloTest extends TestCase
{
    public function setUp(): void
    {
        Silo::$data = [];
    }

    public function testSetAndGet()
    {
        Silo::set('foo', 'bar');
        $this->assertEquals('bar', Silo::get('foo'));
    }

    public function testSetArray()
    {
        Silo::set([
            'a' => 'A',
            'b' => 'B'
        ]);

        $this->assertEquals(['a' => 'A', 'b' => 'B'], Silo::get());
    }

    public function testGetArray()
    {
        Silo::set('a', 'A');
        Silo::set('b', 'B');

        $this->assertEquals(['a' => 'A', 'b' => 'B'], Silo::get());
    }

    public function testRemoveByKey()
    {
        Silo::set('a', 'A');
        $this->assertEquals('A', Silo::get('a'));
        Silo::remove('a');
        $this->assertNull(Silo::get('a'));
    }

    public function testRemoveAll()
    {
        Silo::set('a', 'A');
        Silo::set('b', 'B');
        $this->assertEquals('A', Silo::get('a'));
        $this->assertEquals('B', Silo::get('b'));
        Silo::remove();
        $this->assertNull(Silo::get('a'));
        $this->assertNull(Silo::get('b'));
    }
}
