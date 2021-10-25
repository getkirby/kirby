<?php

namespace Kirby\Parsley;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Parsley\Schema
 */
class SchemaTest extends TestCase
{
    /**
     * @covers ::fallback
     */
    public function testFallback()
    {
        $schema = new Schema();
        return $this->assertNull($schema->fallback('test'));
    }

    /**
     * @covers ::marks
     */
    public function testMarks()
    {
        $schema = new Schema();
        return $this->assertSame([], $schema->marks());
    }

    /**
     * @covers ::nodes
     */
    public function testNodes()
    {
        $schema = new Schema();
        return $this->assertSame([], $schema->nodes());
    }

    /**
     * @covers ::skip
     */
    public function testSkip()
    {
        $schema = new Schema();
        return $this->assertSame([], $schema->skip());
    }
}
