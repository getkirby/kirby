<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;

class AppLocalesTest extends \PHPUnit\Framework\TestCase
{

    public function testException()
    {
        $exception = new Exception([
            'key'      => 'test',
            'fallback' => 'This would be the fallback error'
        ]);

        $this->assertEquals('exception.test', $exception->getKey());
        $this->assertEquals('This is a test error', $exception->getMessage());
    }

    public function testExceptionInvalidKey()
    {
        $exception = new Exception([
            'key'      => 'no-real-key',
            'fallback' => 'This would be the fallback error'
        ]);

        $this->assertEquals('exception.no-real-key', $exception->getKey());
        $this->assertEquals('This would be the fallback error', $exception->getMessage());
    }

}
