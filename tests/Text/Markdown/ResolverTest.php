<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Resolver::class)]
class ResolverTest extends TestCase
{
	protected Resolver $resolver;

	public function setUp(): void
	{
		$this->resolver = new Resolver(new Parser());
	}

	public function testNodeResolvesInlineContent(): void
	{
		$element = new Element(name: 'p', content: 'x *em* y');
		$this->resolver->node($element);

		// the deferred content is parsed into child nodes
		$this->assertNull($element->content);
		$this->assertInstanceOf(Text::class, $element->children[0]);
		$this->assertSame('x ', $element->children[0]->text);

		$em = $element->children[1];
		$this->assertSame('em', $em->name);

		// and resolved recursively
		$this->assertInstanceOf(Text::class, $em->children[0]);
		$this->assertSame('em', $em->children[0]->text);
	}

	public function testNodeResolvesBlockContent(): void
	{
		$element = new Element(name: 'blockquote', content: 'para', block: true);
		$this->resolver->node($element);

		// block content is parsed at block level, wrapped in a paragraph
		$this->assertSame('p', $element->children[0]->name);
		$this->assertSame('para', $element->children[0]->children[0]->text);
	}

	public function testNodeLeavesLeafUntouched(): void
	{
		$text = new Text('x');

		$this->assertSame($text, $this->resolver->node($text));
	}

	public function testNodes(): void
	{
		$a = new Element(name: 'p', content: 'a');
		$b = new Element(name: 'p', content: 'b');

		$this->resolver->nodes([$a, $b]);

		$this->assertSame('a', $a->children[0]->text);
		$this->assertSame('b', $b->children[0]->text);
	}
}
