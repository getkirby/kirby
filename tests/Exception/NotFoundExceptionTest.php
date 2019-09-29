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
        $this->assertSame('error.notFound', $exception->getKey());
        $this->assertSame('Not found', $exception->getMessage());
        $this->assertSame(404, $exception->getHttpCode());
    }
}
