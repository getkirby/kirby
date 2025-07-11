<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LayoutsMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'layoutsMethods' => [
				'test' => fn () => 'layouts method'
			]
		]);
	}

	public function testLayoutsMethod(): void
	{
		$layouts = Layouts::factory();
		$this->assertSame('layouts method', $layouts->test());
	}
}
