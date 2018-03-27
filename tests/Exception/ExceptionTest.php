<?php

namespace Kirby\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testException()
    {
        $exception = new Exception([
            'key' => $key = 'error.page.slug.invalid',
            'fallback' => $fallback = 'The page slug "%s" is invalid',
            'data' => $data = ['project/(c'],
            'code' => $code = 500
        ]);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals($key, $exception->getKey());
        $this->assertEquals('The page slug "project/(c" is invalid', $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($data, $exception->getData());
    }

    public function testDefaults()
    {
        $exception = new Exception();

        $this->assertEquals('error.exception', $exception->getKey());
        $this->assertEquals('An error occured', $exception->getMessage());
        $this->assertEquals(null, $exception->getCode());
        $this->assertEquals([], $exception->getData());
    }

}
