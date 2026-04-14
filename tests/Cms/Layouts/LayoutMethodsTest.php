<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LayoutMethodsTest extends TestCase
{
	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'layoutMethods' => [
				'test' => fn () => 'layout method'
			]
		]);
	}

	public function testLayoutMethod(): void
	{
		$layout = new Layout();
		$this->assertSame('layout method', $layout->test());
	}
}
