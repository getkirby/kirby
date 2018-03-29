<?php

namespace Kirby\Exception;

class WillFail {

    public function fail () {
        throw new Exception([
            'key' => 'key.unique',
        ]);
    }

}

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testException()
    {
        $exception = new Exception([
            'key' => 'page.slug.invalid',
            'fallback' => 'The page slug "{slug}" is invalid',
            'data' => $data = ['slug' => 'project/(c'],
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
        $this->assertEquals('An error occurred', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertEquals([], $exception->getData());
    }

    /**
     * @expectedException Kirby\Exception\Exception
     * @expectedExceptionCode exception.key.unique
     */
    public function testPHPUnitTesting()
    {
        $class = new WillFail();
        $class->fail();
    }

}
