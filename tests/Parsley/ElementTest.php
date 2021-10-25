<?php

namespace Kirby\Parsley;

use Kirby\Toolkit\Dom;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Parsley\Element
 */
class ElementTest extends TestCase
{
    /**
     * @covers ::attr
     */
    public function testAttr()
    {
        $dom     = new Dom('<p class="test">test</p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('test', $element->attr('class'));
    }

    /**
     * @covers ::children
     */
    public function testChildren()
    {
        $dom     = new Dom('<p class="test"><span>A</span><span>B</span></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);
        $children = $element->children();

        $this->assertInstanceOf('DOMNodeList', $children);
        $this->assertCount(2, $children);
    }

    /**
     * @covers ::classList
     */
    public function testClassList()
    {
        $dom     = new Dom('<p class="a b">test</p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame(['a', 'b'], $element->classList());
    }

    /**
     * @covers ::className
     */
    public function testClassName()
    {
        $dom     = new Dom('<p class="a b">test</p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('a b', $element->className());
    }

    /**
     * @covers ::element
     */
    public function testElement()
    {
        $dom     = new Dom('test');
        $body    = $dom->body();
        $element = new Element($body);

        $this->assertSame($body, $element->element());
    }

    /**
     * @covers ::filter
     */
    public function testFilter()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);
        $children = $element->filter('//b');

        $this->assertCount(1, $children);
        $this->assertSame('Bold', $children[0]->innerText());
    }

    /**
     * @covers ::find
     */
    public function testFind()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);
        $child   = $element->find('//b');

        $this->assertSame('Bold', $child->innerText());
    }

    /**
     * @covers ::find
     */
    public function testFindWithoutResult()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertNull($element->find('//a'));
    }

    /**
     * @covers ::innerHtml
     */
    public function testInnerHtml()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('ItalicBold', $element->innerHtml());
    }

    /**
     * @covers ::innerHtml
     */
    public function testInnerHtmlWithMarks()
    {
        $marks = [
            ['tag' => 'i'],
        ];

        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p, $marks);

        $this->assertSame('<i>Italic</i>Bold', $element->innerHtml());

        $marks = [
            ['tag' => 'b'],
            ['tag' => 'i'],
        ];

        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p, $marks);

        $this->assertSame('<i>Italic</i><b>Bold</b>', $element->innerHtml());
    }

    /**
     * @covers ::innerText
     */
    public function testInnerText()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('ItalicBold', $element->innerText());
    }

    /**
     * @covers ::outerHtml
     */
    public function testOuterHtml()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('<p><i>Italic</i><b>Bold</b></p>', $element->outerHtml());
    }

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $dom = new Dom('<p><i>Italic</i><b>Bold</b></p>');

        $this->assertSame('p', $dom->query('//p')[0]->tagName);
        $this->assertSame('i', $dom->query('//p/i')[0]->tagName);
        $this->assertSame('b', $dom->query('//p/b')[0]->tagName);
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        $dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
        $i       = $dom->query('//p/i')[0];
        $element = new Element($i);
        $element->remove();

        $this->assertNull($dom->query('//p/i')[0]);
    }

    /**
     * @covers ::tagName
     */
    public function testTagName()
    {
        $dom     = new Dom('<p>Test</p>');
        $p       = $dom->query('//p')[0];
        $element = new Element($p);

        $this->assertSame('p', $element->tagName());
    }
}
