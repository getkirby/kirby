<?php

namespace Kirby\Exception;

class WillFail
{
    public function fail()
    {
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
            'fallback' => 'The page slug "{ slug }" is invalid',
            'data' => $data = ['slug' => 'project/(c'],
            'httpCode' => $http = 500,
            'translate' => false
        ]);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('error.page.slug.invalid', $exception->getKey());
        $this->assertEquals('error.page.slug.invalid', $exception->getCode());
        $this->assertEquals('The page slug "project/(c" is invalid', $exception->getMessage());
        $this->assertEquals($http, $exception->getHttpCode());
        $this->assertEquals($data, $exception->getData());
        $this->assertFalse($exception->isTranslated());
    }

    public function testDefaults()
    {
        $exception = new Exception();

        $this->assertEquals('error.general', $exception->getKey());
        $this->assertEquals('An error occurred', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertEquals([], $exception->getData());
    }

    public function testPHPUnitTesting()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionCode('error.key.unique');

        $class = new WillFail();
        $class->fail();
    }

    public function testGetDetails()
    {
        $exception = new Exception([
            'details' => ['test']
        ]);

        $this->assertEquals(['test'], $exception->getDetails());
    }

    public function testToArray()
    {
        $exception = new Exception();

        $expected = [
            'exception' => 'Kirby\Exception\Exception',
            'message'   => 'An error occurred',
            'key'       => 'error.general',
            'file'      => __FILE__,
            'line'      => $exception->getLine(),
            'details'   => [],
            'code'      => 500
        ];

        $this->assertEquals($expected, $exception->toArray());
    }
}
