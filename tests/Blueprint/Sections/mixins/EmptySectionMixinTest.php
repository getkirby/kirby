<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;

class EmptySectionMixinTest extends TestCase
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
			'mixins' => ['empty'],
		];
	}

	public function testDefaultEmpty(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertNull($section->empty());
	}

	public function testEmpty(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $section->empty());
	}

	public function testTranslateEmpty(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
			'empty' => [
				'en' => 'EN',
				'de' => 'DE',
			]
		]);

		$this->assertSame('EN', $section->empty());
	}
}
