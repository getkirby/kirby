<?php

namespace Kirby\Parsley\Schema;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Parsley\Schema\Plain
 */
class PlainTest extends TestCase
{
    /**
     * @covers ::fallback
     */
    public function testFallback()
    {
        $schema = new Plain();
        $expected = [
            'type' => 'text',
            'content' => [
                'text' => 'Test'
            ]
        ];

        return $this->assertSame($expected, $schema->fallback('Test'));
    }

    /**
     * @covers ::marks
     */
    public function testMarks()
    {
        $schema = new Plain();
        return $this->assertSame([], $schema->marks());
    }

    /**
     * @covers ::nodes
     */
    public function testNodes()
    {
        $schema = new Plain();
        return $this->assertSame([], $schema->nodes());
    }

    /**
     * @covers ::skip
     */
    public function testSkip()
    {
        $schema = new Plain();
        return $this->assertSame(['head', 'meta', 'script', 'style'], $schema->skip());
    }
}
