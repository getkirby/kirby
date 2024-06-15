<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class MaxSectionMixinTest extends TestCase
{
	protected Page $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->page = new Page(['slug' => 'test']);

		Section::$types['test'] = [
			'mixins'   => ['max'],
			'computed' => [
				'total' => fn () => 10
			]
		];
	}

	public function testDefaultMax()
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertNull($section->max());
	}

	public function testMax()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'max'   => 1
		]);

		$this->assertSame(1, $section->max());
	}

	public function testIsNotFull()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'max'   => 100
		]);

		$this->assertFalse($section->isFull());
		$this->assertTrue($section->validateMax());
	}

	public function testIsFull()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'max'   => 1
		]);

		$this->assertTrue($section->isFull());
		$this->assertFalse($section->validateMax());
	}

	public function testIsExactlyFull()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'max'   => 10
		]);

		$this->assertTrue($section->isFull());
		$this->assertTrue($section->validateMax());
	}
}
