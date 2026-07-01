<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
	public function testConstruct(): void
	{
		$child    = new Text('x');
		$document = new Document([$child]);
		$this->assertSame([$child], $document->children);
	}

	public function testHasBreak(): void
	{
		// the root carries no break of its own
		$this->assertFalse((new Document([]))->hasBreak());
	}
}
