<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Html::class)]
class HtmlTest extends TestCase
{
	public function testConstruct(): void
	{
		$html = new Html('<b>');
		$this->assertSame('<b>', $html->html);
		$this->assertFalse($html->trusted);

		$html = new Html('&#160;', trusted: true);
		$this->assertTrue($html->trusted);
	}

	public function testHasBreak(): void
	{
		// a leaf does not break by default
		$this->assertFalse((new Html('<b>'))->hasBreak());

		$this->assertTrue((new Html('<div>', break: true))->hasBreak());
	}
}
