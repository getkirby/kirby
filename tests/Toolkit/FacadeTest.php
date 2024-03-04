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

/**
 * @coversDefaultClass \Kirby\Toolkit\Facade
 */
class FacadeTest extends TestCase
{
	/**
	 * @covers ::__callStatic
	 */
	public function testCall()
	{
		$this->assertSame('Test', ObjFacade::test());
	}
}
