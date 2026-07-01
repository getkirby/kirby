<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Element::class)]
class ElementTest extends TestCase
{
	public function testConstruct(): void
	{
		$element = new Element(
			name:       'a',
			attributes: ['href' => '/x'],
			content:    'text'
		);

		$this->assertSame('a', $element->name);
		$this->assertSame(['href' => '/x'], $element->attributes);
		$this->assertSame('text', $element->content);
		$this->assertNull($element->children);
	}

	public function testConstructThrowsForContentAndChildren(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new Element(name: 'p', children: [new Text('x')], content: 'x');
	}

	public function testHasBreak(): void
	{
		// a named element breaks by default
		$this->assertTrue((new Element(name: 'p'))->hasBreak());

		// a nameless fragment does not
		$this->assertFalse((new Element(name: null))->hasBreak());

		// an explicit break overrides these defaults
		$this->assertFalse((new Element(name: 'p', break: false))->hasBreak());
		$this->assertTrue((new Element(name: null, break: true))->hasBreak());
	}
}
