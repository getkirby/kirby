<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline\Emphasis;
use Kirby\Text\Markdown\Parser;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Stack::class)]
class StackTest extends TestCase
{
	protected Emphasis $inline;

	public function setUp(): void
	{
		$this->inline = new Emphasis(new Parser());
	}

	protected function delimiter(int $length, bool $canOpen, bool $canClose): Delimiter
	{
		return new Delimiter(
			inline:     $this->inline,
			marker:   '*',
			length:   $length,
			canOpen:  $canOpen,
			canClose: $canClose
		);
	}

	/**
	 * Feeds the nodes through a fresh stack and returns the resolved
	 * list, so the emphasis pairing can be tested in isolation.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected function process(array $nodes): array
	{
		$stack = new Stack();

		foreach ($nodes as $node) {
			$stack->add($node);
		}

		return $stack->flatten();
	}

	public function testProcessNoDelimiters(): void
	{
		// a list without any delimiter run is returned untouched
		$input = [new Text('a'), new Text('b')];

		$this->assertSame($input, $this->process($input));
	}

	public function testProcessPair(): void
	{
		$nodes = $this->process([
			$this->delimiter(1, true, false),
			new Text('x'),
			$this->delimiter(1, false, true)
		]);

		$this->assertCount(1, $nodes);
		$this->assertInstanceOf(Element::class, $nodes[0]);
		$this->assertSame('em', $nodes[0]->name);
		$this->assertSame('x', $nodes[0]->children[0]->text);
	}

	public function testProcessPartialConsumption(): void
	{
		// a length-3 opener meeting a length-1 closer emphasizes once
		// and leaves the surplus `**` as literal text before it
		$nodes = $this->process([
			$this->delimiter(3, true, false),
			new Text('x'),
			$this->delimiter(1, false, true)
		]);

		$this->assertCount(2, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('**', $nodes[0]->text);
		$this->assertInstanceOf(Element::class, $nodes[1]);
		$this->assertSame('em', $nodes[1]->name);
		$this->assertSame('x', $nodes[1]->children[0]->text);
	}

	public function testProcessStrong(): void
	{
		$nodes = $this->process([
			$this->delimiter(2, true, false),
			new Text('x'),
			$this->delimiter(2, false, true)
		]);

		$this->assertSame('strong', $nodes[0]->name);
	}

	public function testProcessUnmatched(): void
	{
		// a run that pairs with nothing turns back into literal text
		$nodes = $this->process([
			$this->delimiter(1, true, false),
			new Text('x')
		]);

		$this->assertCount(2, $nodes);
		$this->assertInstanceOf(Text::class, $nodes[0]);
		$this->assertSame('*', $nodes[0]->text);
		$this->assertSame('x', $nodes[1]->text);
	}

	public function testProcessUnmatchedCloser(): void
	{
		// a closer with no opener before it turns literal and unthreads
		$nodes = $this->process([
			new Text('x'),
			$this->delimiter(1, canOpen: false, canClose: true)
		]);

		$this->assertCount(2, $nodes);
		$this->assertSame('x', $nodes[0]->text);
		$this->assertSame('*', $nodes[1]->text);
	}

	public function testOpener(): void
	{
		$stack = new Stack();

		// no open bracket yet
		$this->assertNull($stack->opener());

		// the most recently opened bracket is returned
		$stack->open('[', 1);
		$opener = $stack->opener();

		$this->assertNotNull($opener);
		$this->assertSame('[', $opener->bracket);
	}

	public function testResolvesLinkBracket(): void
	{
		// a `[…](…)` bracket resolves through the stack's open/close pass
		$this->assertSame(
			'<a href="/b">a</a>',
			(new Parser())->parse('[a](/b)', true)
		);
	}

	public function testResolvesImageBracket(): void
	{
		$this->assertSame(
			'<img src="/b" alt="a" />',
			(new Parser())->parse('![a](/b)', true)
		);
	}

	public function testDropsUnresolvedBracket(): void
	{
		// a bracket with no destination is dropped and stays literal
		$this->assertSame('[a]', (new Parser())->parse('[a]', true));
	}

	public function testGenderStarTriggersWhitespaceWalk(): void
	{
		// an intraword `*` pair spanning whitespace triggers the walk and
		// stays literal (the German gender-star rule)
		$this->assertSame('a*b c*d', (new Parser())->parse('a*b c*d', true));
	}

	public function testIntrawordEmphasisWithoutWhitespace(): void
	{
		// an intraword `*` pair with no whitespace between still emphasizes
		$this->assertSame(
			'un<em>believ</em>able',
			(new Parser())->parse('un*believ*able', true)
		);
	}

	public function testNestedLinkDisablesOuterOpener(): void
	{
		// a formed inner link disables every earlier `[` opener on the stack
		$this->assertSame(
			'[<a href="/b">a</a>](/c)',
			(new Parser())->parse('[[a](/b)](/c)', true)
		);
	}

	public function testLinkAfterEmphasis(): void
	{
		// a link whose floor is a resolved delimiter run detaches from it
		// cleanly (the non-null `floor` branch of `::close()`)
		$this->assertSame(
			'<em>a</em> <a href="/c">b</a>',
			(new Parser())->parse('*a* [b](/c)', true)
		);
	}

	public function testIntrawordEmphasisWrapsNestedElement(): void
	{
		// the whitespace walk descends into a nested element and finds none,
		// so the intraword `*` pair still emphasizes
		$this->assertSame(
			'a<em>x<strong>y</strong>z</em>b',
			(new Parser())->parse('a*x**y**z*b', true)
		);
	}

	public function testIntrawordEmphasisWithBreakStaysLiteral(): void
	{
		// a line break inside the span counts as whitespace, so the
		// intraword `*` pair stays literal
		$this->assertSame(
			"a*x\ny*b",
			(new Parser())->parse("a*x\ny*b", true)
		);
	}
}
