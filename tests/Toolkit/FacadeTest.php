<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;

class ObjFacade extends Facade
{
	public static function instance()
	{
		return new Obj([
			'test' => 'Test'
		]);
	}
}

class FacadeTest extends TestCase
{
	public function testCall()
	{
		$this->assertSame('Test', ObjFacade::test());
	}
}
