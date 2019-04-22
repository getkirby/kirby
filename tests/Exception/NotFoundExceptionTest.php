<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class NotFoundExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new NotFoundException();
        $this->assertEquals('error.notFound', $exception->getKey());
        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getHttpCode());
    }
}
