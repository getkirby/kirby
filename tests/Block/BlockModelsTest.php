<?php

namespace Kirby\Block;

use Kirby\Cms\App;
use Kirby\TestCase;

class HeadingBlock extends Block
{
	public function test(): string
	{
		return $this->id();
	}
}

class TextBlock extends Block
{
	public function test(): string
	{
		return $this->id();
	}
}

class DefaultBlock extends Block
{
	public function test(): string
	{
		return $this->id();
	}
}

class BlockModelsTest extends TestCase
{
	protected array $previousModels;

	public function setUp(): void
	{
		parent::setUp();

		new App([
			'blockModels' => [
				'heading' => HeadingBlock::class
			]
		]);
	}

	public function testBlockModel()
	{
		$block = Block::factory(['type' => 'heading']);

		$this->assertArrayHasKey('heading', Block::$models);
		$this->assertInstanceOf(HeadingBlock::class, $block);
		$this->assertSame($block->id(), $block->test());
	}

	public function testBlockModelFromConfig()
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'blockModels' => [
				'text' => TextBlock::class
			]
		]);

		$block = Block::factory(['type' => 'text']);

		$this->assertArrayHasKey('text', Block::$models);
		$this->assertInstanceOf(TextBlock::class, $block);
		$this->assertSame($block->id(), $block->test());
	}

	public function testMissingBlockModel()
	{
		$block = Block::factory(['type' => 'foo']);

		$this->assertArrayNotHasKey('foo', Block::$models);
		$this->assertInstanceOf(Block::class, $block);
		$this->assertFalse(method_exists($block, 'test'));
	}

	public function testDefaultBlockModel()
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'blockModels' => [
				'default' => DefaultBlock::class
			]
		]);

		$block = Block::factory(['type' => 'test']);
		$this->assertInstanceOf(DefaultBlock::class, $block);
		$this->assertSame($block->id(), $block->test());

		// $block = Block::factory(['type' => 'image']);
		// $this->assertInstanceOf(DefaultBlock::class, $block);
		// $this->assertSame($block->id(), $block->test());

		// $block = Block::factory(['type' => 'list']);
		// $this->assertInstanceOf(DefaultBlock::class, $block);
		// $this->assertSame($block->id(), $block->test());

		// $block = Block::factory(['type' => 'gallery']);
		// $this->assertInstanceOf(DefaultBlock::class, $block);
		// $this->assertSame($block->id(), $block->test());
	}
}
