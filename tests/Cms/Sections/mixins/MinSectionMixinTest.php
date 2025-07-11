<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class MinSectionMixinTest extends TestCase
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
			'mixins'   => ['min'],
			'computed' => [
				'total' => fn () => 10
			]
		];
	}

	public function testDefaultMin()
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertNull($section->min());
	}

	public function testMin()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'min'   => 1
		]);

		$this->assertSame(1, $section->min());
	}

	public function testIsInvalid()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'min'   => 20
		]);

		$this->assertFalse($section->validateMin());
	}

	public function testIsValid()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'min'   => 1
		]);

		$this->assertTrue($section->validateMin());
	}

	public function testIsExactlyValid()
	{
		$section = new Section('test', [
			'model' => $this->page,
			'min'   => 10
		]);

		$this->assertTrue($section->validateMin());
	}
}
