<?php

namespace Kirby\Http\Request\Auth;

use PHPUnit\Framework\TestCase;

class BearerAuthTest extends TestCase
{
    public function testInstance()
    {
        $auth = new BearerAuth('abcd');

        $this->assertEquals('abcd', $auth->token());
        $this->assertEquals('bearer', $auth->type());
        $this->assertEquals('Bearer abcd', $auth->__toString());
    }
}
