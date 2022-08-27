<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
	protected $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
		]);

		$this->page = new Page(['slug' => 'test']);
	}

	public function testConstruct()
	{
		$block = new Block(['type' => 'test']);

		$this->assertInstanceOf('Kirby\Cms\Content', $block->content());
		$this->assertFalse($block->isHidden());
		$this->assertInstanceOf('Kirby\Cms\Blocks', $block->siblings());
		$this->assertSame('test', $block->type());
	}

	public function testConstructWithoutType()
	{
		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('The block type is missing');

		$block = new Block([]);
	}

	public function testContent()
	{
		$block = new Block([
			'type'    => 'heading',
			'content' => $content = [
				'a' => 'Test Field A',
				'b' => 'Test Field B'
			]
		]);

		$this->assertEquals('Test Field A', $block->content()->a());
		$this->assertEquals('Test Field A', $block->a());
		$this->assertEquals('Test Field B', $block->content()->b());
		$this->assertEquals('Test Field B', $block->b());
		$this->assertSame($content, $block->content()->toArray());
	}

	public function testContentWhenNotArray()
	{
		$block = new Block([
			'type'    => 'heading',
			'content' => 'this is invalid now'
		]);

		$this->assertSame([], $block->content()->toArray());
	}

	public function testController()
	{
		$block = new Block([
			'type' => 'heading',
			'content' => [
				'a' => 'Test Content A',
				'b' => 'Test Content B'
			]
		]);

		$this->assertSame($block->content(), $block->controller()['content']);
		$this->assertSame($block, $block->controller()['block']);
		$this->assertSame($block->id(), $block->controller()['id']);
		$this->assertNull($block->controller()['prev']);
		$this->assertNull($block->controller()['next']);
	}

	public function testFactory()
	{
		$block = Block::factory([
			'type' => 'heading'
		]);

		$this->assertInstanceOf('Kirby\Cms\Block', $block);
	}

	public function testIsEmpty()
	{
		$block = new Block([
			'type' => 'heading'
		]);

		$this->assertTrue($block->isEmpty());
		$this->assertFalse($block->isNotEmpty());

		$block = new Block([
			'type' => 'heading',
			'content' => [
				'text' => 'This is a nice heading'
			]
		]);

		$this->assertFalse($block->isEmpty());
		$this->assertTrue($block->isNotEmpty());
	}

	public function testIsHidden()
	{
		$block = new Block([
			'type' => 'heading',
			'isHidden' => true
		]);

		$this->assertTrue($block->isHidden());
	}

	public function testParent()
	{
		$block = new Block([
			'parent' => $page = new Page(['slug' => 'test']),
			'type'   => 'heading'
		]);

		$this->assertSame($page, $block->content()->parent());
	}

	public function testToArray()
	{
		$block = new Block([
			'type' => 'heading',
			'content' => $content = [
				'a' => 'Test Content A',
				'b' => 'Test Content B'
			]
		]);

		$this->assertSame([
			'content'  => $content,
			'id'       => $block->id(),
			'isHidden' => false,
			'type'     => 'heading'
		], $block->toArray());
	}

	public function testToField()
	{
		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'heading',
		]);

		$expected = "<h2>Test</h2>\n";

		$this->assertInstanceOf('Kirby\Cms\Field', $block->toField());
		$this->assertSame($block->parent(), $block->toField()->parent());
		$this->assertSame($block->id(), $block->toField()->key());
		$this->assertSame($expected, $block->toField()->value());
	}

	public function testToHtml()
	{
		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'heading',
		]);

		$expected = "<h2>Test</h2>\n";

		$this->assertSame($expected, $block->toHtml());
		$this->assertSame($expected, $block->__toString());
		$this->assertSame($expected, (string)$block);
	}

	public function testToHtmlInvalid()
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => __DIR__ . '/fixtures/snippets'
			]
		]);

		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'debug'
		]);

		$this->assertSame('', $block->toHtml());
	}

	public function testToHtmlInvalidWithDebugMode()
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => __DIR__ . '/fixtures/snippets'
			],
			'options' => [
				'debug' => true
			]
		]);

		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'debug'
		]);

		$expected = '<p>Block error: "Call to undefined function shouldThrowException()" in block type: "debug"</p>';
		$this->assertSame($expected, $block->toHtml());
	}

	public function testToHtmlWithCustomSnippets()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => __DIR__ . '/fixtures/snippets'
			],
		]);

		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'text'
		]);

		$expected = "<p class=\"custom-text\">Test</p>\n";

		$this->assertSame($expected, $block->toHtml());
		$this->assertSame($expected, $block->__toString());
		$this->assertSame($expected, (string)$block);
	}

	public function testExcerpt()
	{
		$block = new Block([
			'content' => [
				'text' => $expected = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
			],
			'type' => 'text',
		]);

		$this->assertSame($expected, $block->toHtml());
		$this->assertSame($expected, $block->excerpt());
		$this->assertSame('Lorem ipsum dolor …', $block->excerpt(20));
		$this->assertSame($expected, (string)$block);
	}

	public function samplePrevNextBlocks()
	{
		return Blocks::factory([
			[
				'type' => 'code',
				'isHidden' => true
			],
			[
				'type' => 'gallery',
				'isHidden' => true
			],
			[
				'type' => 'heading',
				'isHidden' => false
			],
			[
				'type' => 'image',
				'isHidden' => false
			],
			[
				'type' => 'line',
				'isHidden' => false
			],
			[
				'type' => 'list',
				'isHidden' => true
			],
			[
				'type' => 'markdown',
				'isHidden' => false
			],
			[
				'type' => 'quote',
				'isHidden' => true
			],
			[
				'type' => 'table',
				'isHidden' => false
			],
			[
				'type' => 'text',
				'isHidden' => true
			],
			[
				'type' => 'video',
				'isHidden' => false
			],
		]);
	}

	public function testHiddenSiblings()
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->first();

		$this->assertCount(5, $block->siblings());
		$this->assertNull($block->prev());
	}

	public function testVisibleSiblings()
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->last();

		$this->assertCount(6, $block->siblings());
		$this->assertNull($block->next());
	}

	public function testPrevNextVisible()
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->nth(4);

		$this->assertSame('line', $block->type());
		$this->assertFalse($block->isHidden());
		$this->assertSame('image', $block->prev()->type());
		$this->assertSame('markdown', $block->next()->type());
	}

	public function testPrevNextHidden()
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->nth(5);

		$this->assertSame('list', $block->type());
		$this->assertTrue($block->isHidden());
		$this->assertSame('gallery', $block->prev()->type());
		$this->assertSame('quote', $block->next()->type());
	}
}
