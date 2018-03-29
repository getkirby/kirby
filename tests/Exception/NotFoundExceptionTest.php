<?php

namespace Kirby\Exception;

class NotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testDefaults()
    {
        $exception = new NotFoundException();
        $this->assertEquals('exception.notFound', $exception->getKey());
        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getHttpCode());
    }

}
