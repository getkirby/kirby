<?php

namespace Kirby\Parsley;

use Kirby\Filesystem\F;
use Kirby\Parsley\Schema\Blocks;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\TestCase;

class TestableParsley extends Parsley
{
    public function setBlocks(array $blocks)
    {
        $this->blocks = $blocks;
    }
}


/**
 * @coversDefaultClass \Kirby\Parsley\Parsley
 */
class ParsleyTest extends TestCase
{
    protected function parser(string $html = 'Test')
    {
        return new TestableParsley($html, new Blocks());
    }

    /**
     * @covers ::blocks
     * @covers ::endInlineBlock
     * @covers ::fallback
     * @covers ::mergeOrAppend
     */
    public function testBlocks()
    {
        $examples = glob(__DIR__ . '/fixtures/*.html');

        foreach ($examples as $example) {
            $input    = F::read($example);
            $expected = require_once dirname($example) . '/' . F::name($example) . '.php';
            $output   = $this->parser($input)->blocks();

            $this->assertSame($expected, $output, basename($example));
        }
    }

    public function containsBlockProvider()
    {
        return [
            ['<h1>Test</h1>', '//h1/text()', false],
            ['<h1>Test</h1>', '/html', true],
            ['<h1>Test</h1>', '/html/body', true],
            ['<h1>Test</h1>', '/html/body/h1', false],
            ['<div><h1>Test</h1></div>', '/html/body/div', true],
            ['<div><div><h1>Test</h1></div></div>', '/html/body/div', true],
        ];
    }

    /**
     * @dataProvider containsBlockProvider
     * @covers ::containsBlock
     */
    public function testContainsBlock($html, $query, $expected)
    {
        $dom     = new Dom($html);
        $element = $dom->query($query)[0];

        $this->assertSame($expected, $this->parser()->containsBlock($element));
    }

    /**
     * @covers ::containsBlock
     */
    public function testContainsBlockWithText()
    {
        $dom     = new Dom('Test');
        $element = $dom->query('//body')[0]->childNodes[0];

        $this->assertFalse($this->parser()->containsBlock($element));
    }

    public function isBlockProvider()
    {
        return [
            ['<h1>Test</h1>', '/html/body/h1', true],
            ['<span>Test</span>', '/html/body/span', false],
        ];
    }

    /**
     * @covers ::fallback
     */
    public function testFallbackWithEmptyInput()
    {
        $this->assertNull($this->parser()->fallback([]));
    }

    /**
     * @dataProvider isBlockProvider
     * @covers ::isBlock
     */
    public function testIsBlock($html, $query, $expected)
    {
        $dom     = new Dom($html);
        $element = $dom->query($query)[0];

        $this->assertSame($expected, $this->parser()->isBlock($element));
    }

    public function isInlineProvider()
    {
        return [
            ['<p>Test</p>', '/html/body/p/text()', true],
            ['<p>Test</p>', '/html/body/p', false],
            ['<span>Test</span>', '/html/body/span', true],
            ['<i><h1>Test</h1></i>', '/html/body/i', false],
        ];
    }

    /**
     * @dataProvider isInlineProvider
     * @covers ::isInline
     */
    public function testIsInline($html, $query, $expected)
    {
        $dom     = new Dom($html);
        $element = $dom->query($query)[0];

        $this->assertSame($expected, $this->parser()->isInline($element));
    }

    /**
     * @covers ::isInline
     */
    public function testIsInlineWithComment()
    {
        $dom     = new Dom('<p><!-- test --></p>');
        $comment = $dom->query('/html/body/p')[0]->childNodes[0];

        $this->assertFalse($this->parser()->isInline($comment));
    }

    /**
     * @covers ::mergeOrAppend
     */
    public function testMergeOrAppendExpectMerge()
    {
        $parser = $this->parser();

        $parser->setBlocks([
            [
                'content' => ['text' => '<p>A</p>'],
                'type'    => 'text',
            ]
        ]);

        $parser->mergeOrAppend([
            'content' => ['text' => '<p>B</p>'],
            'type'    => 'text'
        ]);

        $expected = [
            [
                'content' => [
                    'text' => '<p>A</p> <p>B</p>'
                ],
                'type' => 'text'
            ]
        ];

        $this->assertSame($expected, $parser->blocks());
    }

    /**
     * @covers ::mergeOrAppend
     */
    public function testMergeOrAppendExpectAppend()
    {
        $parser = $this->parser();

        $parser->setBlocks([
            [
                'content' => ['text' => 'A'],
                'type'    => 'heading',
            ]
        ]);

        $parser->mergeOrAppend([
            'content' => ['text' => '<p>B</p>'],
            'type'    => 'text'
        ]);

        $expected = [
            [
                'content' => [
                    'text' => 'A'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => '<p>B</p>'
                ],
                'type' => 'text'
            ]
        ];

        $this->assertSame($expected, $parser->blocks());
    }

    /**
     * @covers ::mergeOrAppend
     */
    public function testMergeOrAppendWithoutBlocks()
    {
        $parser = $this->parser();

        $parser->setBlocks([]);

        $parser->mergeOrAppend([
            'content' => ['text' => '<p>B</p>'],
            'type'    => 'text'
        ]);

        $expected = [
            [
                'content' => [
                    'text' => '<p>B</p>'
                ],
                'type' => 'text'
            ]
        ];

        $this->assertSame($expected, $parser->blocks());
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithBlock()
    {
        $dom = new Dom('<p>Test</p>');
        $p   = $dom->query('/html/body/p')[0];

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertTrue($this->parser()->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithComment()
    {
        $dom = new \DOMDocument();
        $dom->loadHTML('<!-- comment -->');

        $comment = $dom->childNodes[1];

        $this->assertInstanceOf('DOMComment', $comment);
        $this->assertFalse($this->parser()->parseNode($comment));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithDoctype()
    {
        $dom = new \DOMDocument();
        $dom->loadHTML('<!doctype html>');

        $this->assertFalse($this->parser()->parseNode($dom->doctype));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithSkippableElement()
    {
        $dom    = new Dom('<script src="/test.js"></script>');
        $script = $dom->query('/html/body/script')[0];

        $this->assertInstanceOf('DOMElement', $script);
        $this->assertFalse($this->parser()->parseNode($script));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithText()
    {
        $dom = new Dom('Test');

        // html > body > text
        $text = $dom->query('/html/body')[0]->childNodes[0];

        $this->assertInstanceOf('DOMText', $text);
        $this->assertTrue($this->parser()->parseNode($text));
    }

    public function testSkipXmlExtension()
    {
        Parsley::$useXmlExtension = false;

        $output   = $this->parser('Test')->blocks();
        $expected = [
            [
                'type' => 'markdown',
                'content' => [
                    'text' => 'Test'
                ]
            ]
        ];

        $this->assertSame($output, $expected);

        // revert the global change
        Parsley::$useXmlExtension = true;
    }
}
