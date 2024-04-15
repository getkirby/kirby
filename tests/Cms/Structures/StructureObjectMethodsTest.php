<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class StructureObjectMethodsTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'structureObjectMethods' => [
				'test' => function () {
					return 'structure object method';
				}
			]
		]);
	}

	public function testBlockMethod()
	{
		$structure = new StructureObject();
		$this->assertSame('structure object method', $structure->test());
	}
}
