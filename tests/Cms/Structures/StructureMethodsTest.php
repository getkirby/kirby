<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class StructureMethodsTest extends TestCase
{
	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'structureMethods' => [
				'test' => fn () => 'structure method'
			]
		]);
	}

	public function testBlocksMethod(): void
	{
		$blocks = Structure::factory([]);
		$this->assertSame('structure method', $blocks->test());
	}
}
