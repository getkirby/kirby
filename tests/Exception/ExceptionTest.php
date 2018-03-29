<?php

namespace Kirby\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testException()
    {
        $exception = new Exception([
            'key' => 'page.slug.invalid',
            'fallback' => 'The page slug "%s" is invalid',
            'data' => $data = ['project/(c'],
            'httpCode' => $http = 500
        ]);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('exception.page.slug.invalid', $exception->getKey());
        $this->assertEquals('exception.page.slug.invalid', $exception->getCode());
        $this->assertEquals('The page slug "project/(c" is invalid', $exception->getMessage());
        $this->assertEquals($http, $exception->getHttpCode());
        $this->assertEquals($data, $exception->getData());
    }

    public function testDefaults()
    {
        $exception = new Exception();

        $this->assertEquals('exception.error', $exception->getKey());
        $this->assertEquals('An error occured', $exception->getMessage());
        $this->assertEquals(0, $exception->getHttpCode());
        $this->assertEquals([], $exception->getData());
    }

}
