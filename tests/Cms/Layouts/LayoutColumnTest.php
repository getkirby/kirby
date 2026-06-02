<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LayoutColumnTest extends TestCase
{
	protected function tearDown(): void
	{
		parent::tearDown();
		LayoutColumn::$methods = [];
	}

	public function testConstruct(): void
	{
		$column = new LayoutColumn();
		$this->assertInstanceOf(Blocks::class, $column->blocks());
		$this->assertSame('1/1', $column->width());
		$this->assertSame(12, $column->span());
	}

	public function testCall(): void
	{
		// registered method
		LayoutColumn::$methods['custom'] = fn () => 'method result';
		LayoutColumn::$methods['echo']   = fn ($value) => $value;

		$column = new LayoutColumn();

		$this->assertSame('method result', $column->custom());
		$this->assertSame('hello', $column->echo('hello'));

		// unknown method returns null
		$this->assertNull($column->unknown());
	}

	public function testBlocks(): void
	{
		$column = new LayoutColumn([
			'blocks' => [
				['type' => 'heading'],
				['type' => 'text'],
			]
		]);
		$this->assertCount(2, $column->blocks());
		$this->assertSame('heading', $column->blocks()->first()->type());
		$this->assertSame('text', $column->blocks()->last()->type());
	}

	public function testHiddenBlocks(): void
	{
		$column = new LayoutColumn([
			'blocks' => [
				['type' => 'heading'],
				['type' => 'text', 'isHidden' => true],
			]
		]);

		$this->assertFalse($column->isEmpty());
		$this->assertTrue($column->isNotEmpty());
		$this->assertCount(1, $column->blocks());
		$this->assertCount(2, $column->blocks(true));
	}

	public function testSpan(): void
	{
		$column = new LayoutColumn([
			'width' => '1/2'
		]);

		$this->assertSame(6, $column->span());
		$this->assertSame(3, $column->span(6));
	}

	public function testWidth(): void
	{
		$column = new LayoutColumn([
			'width' => '1/2'
		]);

		$this->assertSame('1/2', $column->width());
	}

	public function testIsEmpty(): void
	{
		$column = new LayoutColumn([
			'blocks' => []
		]);

		$this->assertTrue($column->isEmpty());
		$this->assertFalse($column->isNotEmpty());
	}

	public function testIsNotEmpty(): void
	{
		$column = new LayoutColumn([
			'blocks' => [
				['type' => 'heading'],
				['type' => 'text']
			]
		]);

		$this->assertFalse($column->isEmpty());
		$this->assertTrue($column->isNotEmpty());
	}
}
