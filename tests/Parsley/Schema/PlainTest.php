<?php

namespace Kirby\Parsley\Schema;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Parsley\Schema\Plain
 */
class PlainTest extends TestCase
{
    protected $schema;

    public function setUp(): void
    {
        $this->schema = new Plain();
    }

    /**
     * @covers ::fallback
     */
    public function testFallback()
    {
        $expected = [
            'type' => 'text',
            'content' => [
                'text' => 'Test'
            ]
        ];

        return $this->assertSame($expected, $this->schema->fallback('Test'));
    }

    /**
     * @covers ::fallback
     */
    public function testFallbackForEmptyContent()
    {
        return $this->assertNull($this->schema->fallback(''));
    }

    /**
     * @covers ::marks
     */
    public function testMarks()
    {
        return $this->assertSame([], $this->schema->marks());
    }

    /**
     * @covers ::nodes
     */
    public function testNodes()
    {
        return $this->assertSame([], $this->schema->nodes());
    }

    /**
     * @covers ::skip
     */
    public function testSkip()
    {
        return $this->assertSame(['head', 'meta', 'script', 'style'], $this->schema->skip());
    }
}
