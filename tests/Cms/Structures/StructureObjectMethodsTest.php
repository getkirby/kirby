<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class StructureObjectMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'structureObjectMethods' => [
				'test' => fn () => 'structure object method'
			]
		]);
	}

	public function testBlockMethod()
	{
		$structure = new StructureObject();
		$this->assertSame('structure object method', $structure->test());
	}
}
