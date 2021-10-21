<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Parsley\Schema\Plain
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
            'content' => [
                'text' => 'Test'
            ],
            'type' => 'text',
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
     * @covers ::fallback
     */
    public function testFallbackForDomElement()
    {
        $dom      = new Dom('<p>Test</p>');
        $p        = $dom->query('//p')[0];
        $el       = new Element($p);
        $fallback = $this->schema->fallback($el);

        $expected = [
            'content' => [
                'text' => 'Test',
            ],
            'type' => 'text'
        ];

        $this->assertSame($expected, $fallback);
    }

    /**
     * @covers ::fallback
     */
    public function testFallbackForInvalidContent()
    {
        $this->assertNull($this->schema->fallback([]));
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
        return $this->assertSame([
            'base',
            'link',
            'meta',
            'script',
            'style',
            'title'
        ], $this->schema->skip());
    }
}
