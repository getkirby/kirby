<?php

namespace Kirby\Block;

use Kirby\Cms\App;
use Kirby\TestCase;

class BlockMethodsTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'blockMethods' => [
				'test' => fn () => 'block method'
			]
		]);
	}

	public function testBlockMethod()
	{
		$block = new Block(['type' => 'test']);
		$this->assertSame('block method', $block->test());
	}
}
