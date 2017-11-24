<?php

namespace Kirby\Http\Router;

use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{

    public function testConstruct()
    {
        $result = new Result('/', 'POST', $func = function () {}, ['a', 'b']);

        $this->assertEquals('/', $result->pattern());
        $this->assertEquals('POST', $result->method());
        $this->assertEquals($func, $result->action());
        $this->assertEquals(['a', 'b'], $result->arguments());
    }
}
