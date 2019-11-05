<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Kirby\Session\SessionStore
 */
class SessionStoreTest extends TestCase
{
    /**
     * @covers ::generateId
     */
    public function testGenerateId()
    {
        // get a reference to the protected method
        $reflector = new ReflectionClass(SessionStore::class);
        $generateId = $reflector->getMethod('generateId');
        $generateId->setAccessible(true);

        $id1 = $generateId->invoke(null);
        $this->assertStringMatchesFormat('%x', $id1);
        $this->assertSame(20, strlen($id1));

        $id2 = $generateId->invoke(null);
        $this->assertStringMatchesFormat('%x', $id2);
        $this->assertSame(20, strlen($id2));
        $this->assertNotSame($id1, $id2);
    }
}
