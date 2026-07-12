<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkDefinition::class)]
class LinkDefinitionTest extends TestCase
{
	protected Parser $parser;
	protected LinkDefinition $block;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->block  = new LinkDefinition($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(LinkDefinition::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['[id]: http://example.com/ "Optional Title Here"']);

		// a definition produces no output but is stored for later use
		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'url'   => 'http://example.com/',
			'title' => 'Optional Title Here'
		], $this->parser->data()->get('LinkDefinition', 'id'));
		$this->assertFalse($line->valid());
	}

	public function testConsumeWithoutTitle(): void
	{
		$line = new Line(['[id]: http://example.com/']);

		$this->block->consume($line);
		$this->assertSame([
			'url'   => 'http://example.com/',
			'title' => null
		], $this->parser->data()->get('LinkDefinition', 'id'));
	}

	public function testConsumeLowercasesId(): void
	{
		// ids are stored case-insensitively
		$line = new Line(['[ID]: http://example.com/']);

		$this->block->consume($line);
		$this->assertNotNull($this->parser->data()->get('LinkDefinition', 'id'));
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

	public function testConsumeMultiLine(): void
	{
		// the destination and title may follow on their own lines
		$line = new Line(['[id]:', '  http://example.com/', '  "Title"']);

		$this->assertNull($this->block->consume($line));
		$this->assertSame([
			'url'   => 'http://example.com/',
			'title' => 'Title'
		], $this->parser->data()->get('LinkDefinition', 'id'));
		$this->assertFalse($line->valid());
	}

	public function testConsumeParenthesizedTitle(): void
	{
		$line = new Line(['[id]: /url (Title)']);

		$this->block->consume($line);
		$this->assertSame('Title', $this->parser->data()->get('LinkDefinition', 'id')['title']);
	}

	public function testConsumeNormalizesDestination(): void
	{
		// the destination is entity-decoded and percent-encoded
		$line = new Line(['[id]: </my uri>']);

		$this->block->consume($line);
		$this->assertSame('/my%20uri', $this->parser->data()->get('LinkDefinition', 'id')['url']);
	}

	public function testConsumeFirstDefinitionWins(): void
	{
		$line = new Line(['[id]: first', '[id]: second']);

		$this->block->consume($line);
		$this->block->consume($line);
		$this->assertSame('first', $this->parser->data()->get('LinkDefinition', 'id')['url']);
	}

	public function testConsumeDoesNotInterruptParagraph(): void
	{
		// a definition may not interrupt a running paragraph
		$line = new Line(['text', '[id]: /url']);
		$line->next();
		$paragraph = new Element(name: 'p', content: 'text');

		$this->assertFalse($this->block->consume($line, $paragraph));
	}

	public function testConsumeTrailingContentIsNotDefinition(): void
	{
		// content after the destination voids the definition
		$line = new Line(['[id]: /url nonsense']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeEmptyLabel(): void
	{
		// a label that folds to an empty string (here whitespace only) is
		// not a definition
		$this->assertFalse($this->block->consume(new Line(['[ ]: /url'])));
	}

	public function testConsumeMissingDestination(): void
	{
		// a label with nothing after the colon is not a definition
		$this->assertFalse($this->block->consume(new Line(['[id]:'])));
	}

	public function testConsumeMalformedDestination(): void
	{
		// an unclosed angle-bracketed destination is not a definition
		$this->assertFalse($this->block->consume(new Line(['[id]: <bad'])));
	}

	public function testConsumeTitleWithTrailingJunk(): void
	{
		// a title followed by junk is dropped; the URL alone must then fill
		// the line, which it does not here, so the definition is voided
		$this->assertFalse($this->block->consume(new Line(['[id]: /url "t" junk'])));
	}
}
