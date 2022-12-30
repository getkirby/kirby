<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

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
