<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class ErrorPageExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new ErrorPageException();
        $this->assertEquals('error.errorPage', $exception->getKey());
        $this->assertEquals('Triggered error page', $exception->getMessage());
        $this->assertEquals(404, $exception->getHttpCode());
    }
}
