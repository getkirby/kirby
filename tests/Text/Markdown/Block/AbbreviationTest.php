<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Abbreviation::class)]
class AbbreviationTest extends TestCase
{
	protected Parser $parser;
	protected Abbreviation $block;

	public function setUp(): void
	{
		$this->parser = new Parser();
		$this->block  = new Abbreviation($this->parser);
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(Abbreviation::class);
	}

	public function testConsume(): void
	{
		$line = new Line(['*[HTML]: Hyper Text Markup Language']);

		// the definition is stored and produces no output
		$this->assertNull($this->block->consume($line));
		$this->assertSame(
			'Hyper Text Markup Language',
			$this->parser->data()->get('Abbreviation', 'HTML')
		);

		$this->assertFalse($line->valid());
	}

	public function testConsumeNoDefinition(): void
	{
		// a plain emphasis line shares the `*` marker
		// but is not a definition
		$line = new Line(['*emphasis*']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeMissingMeaning(): void
	{
		$line = new Line(['*[HTML] no colon']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testTransform(): void
	{
		$this->parser->data()->set('Abbreviation', 'HTML', 'Hyper Text Markup Language');

		$nodes  = [new Text('I like HTML')];
		$result = $this->block->transform($nodes);

		// the text node is wrapped in a fragment
		// with the abbreviation tagged
		$this->assertInstanceOf(Element::class, $result[0]);
		$this->assertNull($result[0]->name);

		$abbr = $result[0]->children[1];
		$this->assertInstanceOf(Element::class, $abbr);
		$this->assertSame('abbr', $abbr->name);
		$this->assertSame(['title' => 'Hyper Text Markup Language'], $abbr->attributes);
		$this->assertSame('HTML', $abbr->children[0]->text);
	}

	public function testTransformDescendsIntoChildren(): void
	{
		$this->parser->data()->set('Abbreviation', 'HTML', 'Hyper Text Markup Language');

		// the transform recurses into an element's children
		$element = new Element(name: 'p', children: [new Text('I like HTML')]);
		$result  = $this->block->transform([$element]);

		// the element itself is returned, its text child is rewritten
		$this->assertSame($element, $result[0]);

		$fragment = $result[0]->children[0];
		$this->assertInstanceOf(Element::class, $fragment);
		$this->assertNull($fragment->name);

		$abbr = $fragment->children[1];
		$this->assertInstanceOf(Element::class, $abbr);
		$this->assertSame('abbr', $abbr->name);
		$this->assertSame(['title' => 'Hyper Text Markup Language'], $abbr->attributes);
		$this->assertSame('HTML', $abbr->children[0]->text);
	}

	public function testTransformLeavesCodeUntouched(): void
	{
		$this->parser->data()->set('Abbreviation', 'HTML', 'Hyper Text Markup Language');

		// code is never rewritten
		$code   = new Element(name: 'code', children: [new Text('HTML')]);
		$result = $this->block->transform([$code]);

		$this->assertSame($code, $result[0]);
	}

	public function testTransformLeavesOtherNodesUntouched(): void
	{
		$this->parser->data()->set('Abbreviation', 'HTML', 'Hyper Text Markup Language');

		// a non-element, non-text node is returned as-is
		$html   = new Html('<span>HTML</span>');
		$result = $this->block->transform([$html]);

		$this->assertSame($html, $result[0]);
	}
}
