<?php

namespace Kirby\Kql;

use Kirby\Exception\PermissionException;

/**
 * @coversDefaultClass \Kirby\Kql\Query
 */
class QueryTest extends TestCase
{
	/**
	 * @covers ::intercept
	 */
	public function testIntercept()
	{
		// non-object
		$query = new Query('foo.bar');
		$result = $query->intercept('test');
		$this->assertSame('test', $result);

		// object
		$object = new TestObject();
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Access to the class "Kirby\Kql\TestObject" is not supported');
		$query->intercept($object);
	}
}
