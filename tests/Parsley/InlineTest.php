<?php

namespace Kirby\Parsley;

use Kirby\Toolkit\Dom;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Parsley\Inline
 */
class InlineTest extends TestCase
{
    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithComment()
    {
        $dom = new \DOMDocument();
        $dom->loadHTML('<!-- comment -->');

        $comment = $dom->childNodes[1];
        $element = new Inline($comment);

        $this->assertInstanceOf('DOMComment', $comment);
        $this->assertSame('', $element->parseNode($comment));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithKnownMarks()
    {
        $dom = new Dom('<p><b>Test</b> <i>Test</i></p>');

        // html > body > p
        $p       = $dom->query('/html/body/p')[0];
        $element = new Inline($p, [
            [
                'tag' => 'b'
            ],
            [
                'tag' => 'i'
            ]
        ]);

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertSame('<b>Test</b> <i>Test</i>', $element->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithKnownMarksWithAttrs()
    {
        $dom = new Dom('<p><a href="https://getkirby.com">Test</a></p>');

        // html > body > p
        $p       = $dom->query('/html/body/p')[0];
        $element = new Inline($p, [
            [
                'tag'   => 'a',
                'attrs' => ['href'],
            ],
        ]);

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertSame('<a href="https://getkirby.com">Test</a>', $element->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithKnownMarksWithAttrDefaults()
    {
        $dom = new Dom('<p><a href="https://getkirby.com">Test</a></p>');

        // html > body > p
        $p       = $dom->query('/html/body/p')[0];
        $element = new Inline($p, [
            [
                'tag'   => 'a',
                'attrs' => ['href', 'rel'],
                'defaults' => [
                    'rel' => 'test'
                ]
            ],
        ]);

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertSame('<a href="https://getkirby.com" rel="test">Test</a>', $element->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithUnkownMarks()
    {
        $dom = new Dom('<p><b>Test</b> <i>Test</i></p>');

        // html > body > p
        $p       = $dom->query('/html/body/p')[0];
        $element = new Inline($p);

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertSame('Test Test', $element->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithSelfClosingElement()
    {
        $dom = new Dom('<p><br></p>');

        // html > body > p
        $p       = $dom->query('/html/body/p')[0];
        $element = new Inline($p, [
            [
                'tag' => 'br'
            ]
        ]);

        $this->assertInstanceOf('DOMElement', $p);
        $this->assertSame('<br />', $element->parseNode($p));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithText()
    {
        $dom = new Dom('Test');

        // html > body > p > text
        $text    = $dom->query('/html/body/p')[0]->childNodes[0];
        $element = new Inline($text);

        $this->assertInstanceOf('DOMText', $text);
        $this->assertSame('Test', $element->parseNode($text));
    }

    /**
     * @covers ::parseNode
     */
    public function testParseNodeWithTextEncoded()
    {
        $dom = new Dom('Test & Test');

        // html > body > p > text
        $text    = $dom->query('/html/body/p')[0]->childNodes[0];
        $element = new Inline($text);

        $this->assertInstanceOf('DOMText', $text);
        $this->assertSame('Test &amp; Test', $element->parseNode($text));
    }
}
