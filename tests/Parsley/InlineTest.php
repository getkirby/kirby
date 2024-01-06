<?php

namespace Kirby\Parsley;

use Kirby\TestCase;
use Kirby\Toolkit\Dom;

/**
 * @coversDefaultClass \Kirby\Parsley\Inline
 */
class InlineTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructTrim()
	{
		$dom    = new Dom('<span> Test </span>');
		$dom    = $dom->query('//span')[0];
		$inline = new Inline($dom);
		$this->assertSame('Test', $inline->innerHtml());

		$dom    = new Dom('<span> </span>');
		$dom    = $dom->query('//span')[0];
		$inline = new Inline($dom);
		$this->assertSame(' ', $inline->innerHtml());
	}

	/**
	 * @covers ::parseAttrs
	 */
	public function testParseAttrs()
	{
		$dom    = new Dom('<b class="foo">Test</b>');
		$b      = $dom->query('//b')[0];
		$attrs  = Inline::parseAttrs($b, [
			'b' => [
				'attrs' => ['class']
			]
		]);

		$this->assertSame(['class' => 'foo'], $attrs);
	}

	/**
	 * @covers ::parseAttrs
	 */
	public function testParseAttrsWithDefaults()
	{
		$dom    = new Dom('<b>Test</b>');
		$b      = $dom->query('//b')[0];
		$attrs  = Inline::parseAttrs($b, [
			'b' => [
				'attrs'    => ['class'],
				'defaults' => ['class' => 'foo']
			]
		]);

		$this->assertSame(['class' => 'foo'], $attrs);
	}

	/**
	 * @covers ::parseAttrs
	 */
	public function testParseAttrsWithIgnoredAttrs()
	{
		$dom    = new Dom('<b class="foo">Test</b>');
		$b      = $dom->query('//b')[0];
		$attrs  = Inline::parseAttrs($b, [
			'b' => true
		]);

		$this->assertSame([], $attrs);
	}

	/**
	 * @covers ::parseInnerHtml
	 */
	public function testParseInnerHtml()
	{
		$dom    = new Dom('<p><b>Bold</b> <i>Italic</i></p>');
		$p      = $dom->query('//p')[0];
		$html   = Inline::parseInnerHtml($p, [
			'b' => true,
			'i' => true
		]);

		$this->assertSame('<b>Bold</b> <i>Italic</i>', $html);
	}

	/**
	 * @covers ::parseInnerHtml
	 */
	public function testParseInnerHtmlWithEmptyParagraph()
	{
		$dom  = new Dom('<p> </p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseInnerHtml($p);

		$this->assertNull($html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithComment()
	{
		$dom = new \DOMDocument();
		$dom->loadHTML('<!-- comment -->');

		$comment = $dom->childNodes[1];
		$html    = Inline::parseNode($comment);

		$this->assertNull($html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithEmptyParagraph()
	{
		$dom  = new Dom('<p> </p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'p' => true
		]);

		$this->assertNull($html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithKnownMarks()
	{
		$dom  = new Dom('<p><b>Test</b> <i>Test</i></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'b' => true,
			'i' => true
		]);

		$this->assertSame('<b>Test</b> <i>Test</i>', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithKnownMarksWithAttrs()
	{
		$dom  = new Dom('<p><a href="https://getkirby.com">Test</a></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'a' => [
				'attrs' => ['href'],
			],
		]);

		$this->assertSame('<a href="https://getkirby.com">Test</a>', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithKnownMarksWithAttrDefaults()
	{
		$dom  = new Dom('<p><a href="https://getkirby.com">Test</a></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'a' => [
				'attrs' => ['href', 'rel'],
				'defaults' => [
					'rel' => 'test'
				]
			],
		]);

		$this->assertSame('<a href="https://getkirby.com" rel="test">Test</a>', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithUnkownMarks()
	{
		$dom     = new Dom('<p><b>Test</b> <i>Test</i></p>');
		$p       = $dom->query('//p')[0];
		$html    = Inline::parseNode($p);

		$this->assertSame('Test Test', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithSelfClosingElement()
	{
		$dom  = new Dom('<p><br></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'br' => true
		]);

		$this->assertSame('<br />', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithText()
	{
		$dom  = new Dom('Test');
		$text = $dom->query('//text()')[0];
		$html = Inline::parseNode($text);

		$this->assertSame('Test', $html);
	}

	/**
	 * @covers ::parseNode
	 */
	public function testParseNodeWithTextEncoded()
	{
		$dom  = new Dom('Test & Test');
		$text = $dom->query('//text()')[0];
		$html = Inline::parseNode($text);

		$this->assertSame('Test &amp; Test', $html);
	}
}
