<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LayoutColumnMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'layoutColumnMethods' => [
				'test' => fn () => 'layout column method'
			]
		]);
	}

	public function testLayoutColumnMethod()
	{
		$column = new LayoutColumn();
		$this->assertSame('layout column method', $column->test());
	}
}
