<?php

namespace Kirby\Http\Request\Auth;

use PHPUnit\Framework\TestCase;

class BasicAuthTest extends TestCase
{
    public function testInstance()
    {
        $auth = new BasicAuth(base64_encode($credentials = 'testuser:testpass'));

        $this->assertEquals($credentials, $auth->credentials());
        $this->assertEquals('testpass', $auth->password());
        $this->assertEquals('basic', $auth->type());
        $this->assertEquals('testuser', $auth->username());
    }
}
