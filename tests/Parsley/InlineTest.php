<?php

namespace Kirby\Parsley;

use DOMDocument;
use Kirby\TestCase;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Inline::class)]
class InlineTest extends TestCase
{
	public function testConstructTrim(): void
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

	public function testParseAttrs(): void
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

	public function testParseAttrsWithDefaults(): void
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

	public function testParseAttrsWithIgnoredAttrs(): void
	{
		$dom    = new Dom('<b class="foo">Test</b>');
		$b      = $dom->query('//b')[0];
		$attrs  = Inline::parseAttrs($b, [
			'b' => true
		]);

		$this->assertSame([], $attrs);
	}

	public function testParseInnerHtml(): void
	{
		$dom    = new Dom('<p><b>Bold</b> <i>Italic</i></p>');
		$p      = $dom->query('//p')[0];
		$html   = Inline::parseInnerHtml($p, [
			'b' => true,
			'i' => true
		]);

		$this->assertSame('<b>Bold</b> <i>Italic</i>', $html);
	}

	public function testParseInnerHtmlWithEmptyParagraph(): void
	{
		$dom  = new Dom('<p> </p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseInnerHtml($p);

		$this->assertNull($html);
	}

	public function testParseNodeWithComment(): void
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<!-- comment -->');

		$comment = $dom->childNodes[1];
		$html    = Inline::parseNode($comment);

		$this->assertNull($html);
	}

	public function testParseNodeWithEmptyParagraph(): void
	{
		$dom  = new Dom('<p> </p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'p' => true
		]);

		$this->assertNull($html);
	}

	public function testParseNodeWithKnownMarks(): void
	{
		$dom  = new Dom('<p><b>Test</b> <i>Test</i></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'b' => true,
			'i' => true
		]);

		$this->assertSame('<b>Test</b> <i>Test</i>', $html);
	}

	public function testParseNodeWithKnownMarksWithAttrs(): void
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

	public function testParseNodeWithKnownMarksWithAttrDefaults(): void
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

	public function testParseNodeWithUnkownMarks(): void
	{
		$dom     = new Dom('<p><b>Test</b> <i>Test</i></p>');
		$p       = $dom->query('//p')[0];
		$html    = Inline::parseNode($p);

		$this->assertSame('Test Test', $html);
	}

	public function testParseNodeWithSelfClosingElement(): void
	{
		$dom  = new Dom('<p><br></p>');
		$p    = $dom->query('//p')[0];
		$html = Inline::parseNode($p, [
			'br' => true
		]);

		$this->assertSame('<br />', $html);
	}

	public function testParseNodeWithText(): void
	{
		$dom  = new Dom('Test');
		$text = $dom->query('//text()')[0];
		$html = Inline::parseNode($text);

		$this->assertSame('Test', $html);
	}

	public function testParseNodeWithTextEncoded(): void
	{
		$dom  = new Dom('Test & Test');
		$text = $dom->query('//text()')[0];
		$html = Inline::parseNode($text);

		$this->assertSame('Test &amp; Test', $html);
	}
}
