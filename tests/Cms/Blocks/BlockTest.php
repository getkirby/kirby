<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

class BlockTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Block';

	protected Page $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
		]);

		$this->page = new Page(['slug' => 'test']);
	}

	public function testConstruct(): void
	{
		$block = new Block(['type' => 'test']);

		$this->assertInstanceOf(Content::class, $block->content());
		$this->assertFalse($block->isHidden());
		$this->assertInstanceOf(Blocks::class, $block->siblings());
		$this->assertSame('test', $block->type());
	}

	public function testConstructWithoutType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The block type is missing');

		$block = new Block([]);
	}

	public function testContent(): void
	{
		$block = new Block([
			'type'    => 'heading',
			'content' => $content = [
				'a' => 'Test Field A',
				'b' => 'Test Field B'
			]
		]);

		$this->assertInstanceOf(Field::class, $block->content()->a());
		$this->assertInstanceOf(Field::class, $block->a());
		$this->assertInstanceOf(Field::class, $block->content()->b());
		$this->assertInstanceOf(Field::class, $block->b());
		$this->assertSame('Test Field A', $block->content()->a()->value());
		$this->assertSame('Test Field A', $block->a()->value());
		$this->assertSame('Test Field B', $block->content()->b()->value());
		$this->assertSame('Test Field B', $block->b()->value());
		$this->assertSame($content, $block->content()->toArray());
	}

	/**
	 * @todo block.converter remove eventually
	 */
	public function testContentWhenNotArrayConvertedAsEditorBlock(): void
	{
		$block = new Block([
			'type'    => 'heading',
			'content' => $content = 'this is old editor content'
		]);

		$this->assertSame($content, $block->content()->toArray()['text']);
	}

	public function testController(): void
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

	public function testFactory(): void
	{
		$block = Block::factory([
			'type' => 'heading'
		]);

		$this->assertInstanceOf(Block::class, $block);
	}

	public function testIsEmpty(): void
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

	public function testIsHidden(): void
	{
		$block = new Block([
			'type' => 'heading',
			'isHidden' => true
		]);

		$this->assertTrue($block->isHidden());
	}

	public function testParent(): void
	{
		$block = new Block([
			'parent' => $page = new Page(['slug' => 'test']),
			'type'   => 'heading'
		]);

		$this->assertIsPage($page, $block->content()->parent());
	}

	public function testToArray(): void
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

	public function testToField(): void
	{
		$block = new Block([
			'content' => [
				'text' => 'Test'
			],
			'type' => 'heading',
		]);

		$expected = "<h2>Test</h2>\n";

		$this->assertInstanceOf(Field::class, $block->toField());
		$this->assertSame($block->parent(), $block->toField()->parent());
		$this->assertSame($block->id(), $block->toField()->key());
		$this->assertSame($expected, $block->toField()->value());
	}

	public function testToHtml(): void
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

	public function testToHtmlInvalid(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => static::FIXTURES . '/snippets'
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

	public function testToHtmlInvalidWithDebugMode(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => static::FIXTURES . '/snippets'
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

	public function testToHtmlWithCustomSnippets(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
				'snippets' => static::FIXTURES . '/snippets'
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

	public function testExcerpt(): void
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

	public function testHiddenSiblings(): void
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->first();

		$this->assertCount(5, $block->siblings());
		$this->assertNull($block->prev());
	}

	public function testVisibleSiblings(): void
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->last();

		$this->assertCount(6, $block->siblings());
		$this->assertNull($block->next());
	}

	public function testPrevNextVisible(): void
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->nth(4);

		$this->assertSame('line', $block->type());
		$this->assertFalse($block->isHidden());
		$this->assertSame('image', $block->prev()->type());
		$this->assertSame('markdown', $block->next()->type());
	}

	public function testPrevNextHidden(): void
	{
		$blocks = $this->samplePrevNextBlocks();
		$block = $blocks->nth(5);

		$this->assertSame('list', $block->type());
		$this->assertTrue($block->isHidden());
		$this->assertSame('gallery', $block->prev()->type());
		$this->assertSame('quote', $block->next()->type());
	}

	public function testImageBlock(): void
	{
		$this->app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::FIXTURES . '/files'
			]
		]);

		// no alt
		$block = new Block([
			'type'    => 'image',
			'content' => [
				'image' => 'foo.jpg'
			]
		]);

		$image = $block->image()->toFile();
		$expected = '<img src="/media/site/' . $image->mediaHash() . '/foo.jpg" alt="">';
		$this->assertStringContainsString($expected, $block->toHtml());

		// image alt
		$block = new Block([
			'type'    => 'image',
			'content' => [
				'image' => 'bar.jpg'
			]
		]);

		$image = $block->image()->toFile();
		$expected = '<img src="/media/site/' . $image->mediaHash() . '/bar.jpg" alt="Sample alt text">';
		$this->assertStringContainsString($expected, $block->toHtml());

		// custom alt
		$block = new Block([
			'type'    => 'image',
			'content' => [
				'alt'   => 'Custom image alt text',
				'image' => 'bar.jpg'
			]
		]);

		$image = $block->image()->toFile();
		$expected = '<img src="/media/site/' . $image->mediaHash() . '/bar.jpg" alt="Custom image alt text">';
		$this->assertStringContainsString($expected, $block->toHtml());
	}
}
