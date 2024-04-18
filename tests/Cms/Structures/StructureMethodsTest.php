<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class StructureMethodsTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'structureMethods' => [
				'test' => function () {
					return 'structure method';
				}
			]
		]);
	}

	public function testBlocksMethod()
	{
		$blocks = Structure::factory([]);
		$this->assertSame('structure method', $blocks->test());
	}
}
