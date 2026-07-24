<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RawHtml::class)]
class RawHtmlTest extends TestCase
{
	protected RawHtml $inline;

	public function setUp(): void
	{
		$this->inline = new RawHtml(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(RawHtml::class);
	}

	public function testConsumeOpeningTag(): void
	{
		$phrase = new Phrase('<span>rest');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('<span>', $node->html);
		$this->assertFalse($node->hasBreak());
		$this->assertSame('rest', $phrase->after());
	}

	public function testConsumeClosingTag(): void
	{
		$phrase = new Phrase('</span>');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('</span>', $node->html);
	}

	public function testConsumeSelfClosingTag(): void
	{
		$phrase = new Phrase('<br/>');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('<br/>', $node->html);
	}

	public function testConsumeComment(): void
	{
		$phrase = new Phrase('<!-- hi -->');
		$node   = $this->inline->consume($phrase);

		$this->assertInstanceOf(Html::class, $node);
		$this->assertSame('<!-- hi -->', $node->html);
	}

	public function testConsumeTagWithAttributes(): void
	{
		$phrase = new Phrase('<a href="#">');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('<a href="#">', $node->html);
	}

	public function testConsumeSafeMode(): void
	{
		// in safe mode raw HTML is never consumed
		$inline   = new RawHtml(new Parser(safe: true));
		$phrase = new Phrase('<span>');

		$this->assertFalse($inline->consume($phrase));
	}

	public function testConsumeUnclosed(): void
	{
		$phrase = new Phrase('<span');

		$this->assertFalse($this->inline->consume($phrase));
	}

	public function testConsumeSpaceAfterBracket(): void
	{
		// `< span>` is not a tag
		$phrase = new Phrase('< span>');

		$this->assertFalse($this->inline->consume($phrase));
	}

	public function testConsumeMultilineTag(): void
	{
		// a tag's whitespace may include line breaks
		$phrase = new Phrase("<a\ndata=\"foo\" >rest");
		$node   = $this->inline->consume($phrase);

		$this->assertSame("<a\ndata=\"foo\" >", $node->html);
	}

	public function testConsumeInvalidTagName(): void
	{
		// a tag name must start with a letter
		$this->assertFalse($this->inline->consume(new Phrase('<33>')));
		$this->assertFalse($this->inline->consume(new Phrase('<__>')));
	}

	public function testConsumeAttributesNeedWhitespace(): void
	{
		// attributes must be separated by whitespace
		$this->assertFalse($this->inline->consume(new Phrase("<a href='b'title=c>")));
	}

	public function testConsumeCdata(): void
	{
		$phrase = new Phrase('<![CDATA[>&<]]>rest');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('<![CDATA[>&<]]>', $node->html);
	}

	public function testConsumeProcessingInstruction(): void
	{
		$phrase = new Phrase('<?php echo $a; ?>rest');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('<?php echo $a; ?>', $node->html);
	}

	public function testConsumeDeclaration(): void
	{
		$phrase = new Phrase('<!ELEMENT br EMPTY>rest');
		$node   = $this->inline->consume($phrase);

		$this->assertSame('<!ELEMENT br EMPTY>', $node->html);
	}
}
