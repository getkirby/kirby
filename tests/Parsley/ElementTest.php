<?php

namespace Kirby\Parsley;

use Kirby\TestCase;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
	public function testAttr(): void
	{
		$dom     = new Dom('<p class="test">test</p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('test', $element->attr('class'));
	}

	public function testChildren(): void
	{
		$dom     = new Dom('<p class="test"><span>A</span><span>B</span></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);
		$children = $element->children();

		$this->assertInstanceOf('DOMNodeList', $children);
		$this->assertCount(2, $children);
	}

	public function testClassList(): void
	{
		$dom     = new Dom('<p class="a b">test</p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame(['a', 'b'], $element->classList());
	}

	public function testClassName(): void
	{
		$dom     = new Dom('<p class="a b">test</p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('a b', $element->className());
	}

	public function testElement(): void
	{
		$dom     = new Dom('test');
		$body    = $dom->body();
		$element = new Element($body);

		$this->assertSame($body, $element->element());
	}

	public function testFilter(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);
		$children = $element->filter('//b');

		$this->assertCount(1, $children);
		$this->assertSame('Bold', $children[0]->innerText());
	}

	public function testFind(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);
		$child   = $element->find('//b');

		$this->assertSame('Bold', $child->innerText());
	}

	public function testFindWithoutResult(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertNull($element->find('//a'));
	}

	public function testInnerHtml(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('ItalicBold', $element->innerHtml());
	}

	public function testInnerHtmlWithMarks(): void
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

	public function testInnerText(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('ItalicBold', $element->innerText());
	}

	public function testOuterHtml(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('<p><i>Italic</i><b>Bold</b></p>', $element->outerHtml());
	}

	public function testQuery(): void
	{
		$dom = new Dom('<p><i>Italic</i><b>Bold</b></p>');

		$this->assertSame('p', $dom->query('//p')[0]->tagName);
		$this->assertSame('i', $dom->query('//p/i')[0]->tagName);
		$this->assertSame('b', $dom->query('//p/b')[0]->tagName);
	}

	public function testRemove(): void
	{
		$dom     = new Dom('<p><i>Italic</i><b>Bold</b></p>');
		$i       = $dom->query('//p/i')[0];
		$element = new Element($i);
		$element->remove();

		$this->assertNull($dom->query('//p/i')[0]);
	}

	public function testTagName(): void
	{
		$dom     = new Dom('<p>Test</p>');
		$p       = $dom->query('//p')[0];
		$element = new Element($p);

		$this->assertSame('p', $element->tagName());
	}
}
