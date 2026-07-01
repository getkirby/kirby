<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Reference::class)]
class ReferenceTest extends TestCase
{
	protected Parser $parser;
	protected Reference $block;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->block  = new Reference($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Reference::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['[id]: http://example.com/ "Optional Title Here"']);

		// a definition produces no output but is stored for later use
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'url'   => 'http://example.com/',
			'title' => 'Optional Title Here'
		], $this->parser->data()->get('Reference', 'id'));
		$this->assertFalse($line->valid());
	}

	public function testConsumeWithoutTitle(): void
	{
		$line = new Line(['[id]: http://example.com/']);

		$this->block->consume($line);
		$this->assertSame([
			'url'   => 'http://example.com/',
			'title' => null
		], $this->parser->data()->get('Reference', 'id'));
	}

	public function testConsumeLowercasesId(): void
	{
		// ids are stored case-insensitively
		$line = new Line(['[ID]: http://example.com/']);

		$this->block->consume($line);
		$this->assertNotNull($this->parser->data()->get('Reference', 'id'));
	}

	public function testConsumeNoClosingBracket(): void
	{
		$line = new Line(['[id: http://example.com/']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeNoDefinition(): void
	{
		$line = new Line(['[id] just some text']);
		$this->assertFalse($this->block->consume($line));
	}
}
