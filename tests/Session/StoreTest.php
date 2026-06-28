<?php

namespace Kirby\Session;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(Store::class)]
class StoreTest extends TestCase
{
	public function testGenerateId(): void
	{
		// get a reference to the protected method
		$reflector = new ReflectionClass(Store::class);
		$generateId = $reflector->getMethod('generateId');

		$id1 = $generateId->invoke(null);
		$this->assertStringMatchesFormat('%x', $id1);
		$this->assertSame(20, strlen($id1));

		$id2 = $generateId->invoke(null);
		$this->assertStringMatchesFormat('%x', $id2);
		$this->assertSame(20, strlen($id2));
		$this->assertNotSame($id1, $id2);
	}
}
