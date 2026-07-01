<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FencedCode::class)]
class FencedCodeTest extends TestCase
{
	protected FencedCode $block;

	public function setUp(): void
	{
		$this->block = new FencedCode(new Parser());
	}

	public function testMarkers(): void
	{
		$this->assertMarkersDispatchComponent(FencedCode::class);
	}

	public function testConsumeBacktickFence(): void
	{
		$line = new Line(['```', 'code here', '```']);
		$node = $this->block->consume($line);

		$this->assertInstanceOf(Element::class, $node);
		$this->assertSame('pre', $node->name);

		$code = $node->children[0];
		$this->assertSame('code', $code->name);
		$this->assertSame([], $code->attributes);
		$this->assertInstanceOf(Text::class, $code->children[0]);
		$this->assertSame('code here', $code->children[0]->text);

		$this->assertFalse($line->valid());
	}

	public function testConsumeTildeFence(): void
	{
		$line = new Line(['~~~', 'code', '~~~']);
		$node = $this->block->consume($line);

		$this->assertSame('code', $node->children[0]->children[0]->text);
	}

	public function testConsumeInfoString(): void
	{
		// the first word of the info string becomes a language class
		$line = new Line(['```php', "echo 'hi';", '```']);
		$node = $this->block->consume($line);

		$this->assertSame(
			['class' => 'language-php'],
			$node->children[0]->attributes
		);
		$this->assertSame("echo 'hi';", $node->children[0]->children[0]->text);
	}

	public function testConsumeMultiLine(): void
	{
		$line = new Line(['```', 'a', 'b', '```']);
		$node = $this->block->consume($line);

		$this->assertSame("a\nb", $node->children[0]->children[0]->text);
	}

	public function testConsumeTooFewMarkers(): void
	{
		// fewer than three fence characters is not a fenced block
		$line = new Line(['``', 'x', '``']);
		$this->assertFalse($this->block->consume($line));
	}

	public function testConsumeBacktickInInfoString(): void
	{
		// a backtick in the info string rules out the fence (avoids
		// misreading an inline code run as an opening fence)
		$line = new Line(['```foo`bar']);
		$this->assertFalse($this->block->consume($line));
	}
}
