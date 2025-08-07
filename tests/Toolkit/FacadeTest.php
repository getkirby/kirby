<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;

class ObjFacade extends Facade
{
	public static function instance(): Obj
	{
		return new Obj([
			'test' => 'Test'
		]);
	}
}

class FacadeTest extends TestCase
{
	public function testCall(): void
	{
		$this->assertSame('Test', ObjFacade::test());
	}
}
