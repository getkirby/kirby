<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class DuplicateExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new DuplicateException();
        $this->assertEquals('error.duplicate', $exception->getKey());
        $this->assertEquals('The entry exists', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
    }
}
