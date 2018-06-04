<?php

namespace Kirby\Cms;

use Kirby\Toolkit\QueryTestUser;

class TempuraTest extends TestCase
{

    public function testRenderWithString()
    {
        $tempura = new Tempura('Hello {{ user }}', [
            'user' => 'homer'
        ]);

        $this->assertEquals('Hello homer', $tempura->render());
    }

    public function testRenderWithArray()
    {
        $tempura = new Tempura('Hello {{ user.username }}', [
            'user' => [
                'username' => 'homer'
            ]
        ]);

        $this->assertEquals('Hello homer', $tempura->render());
    }

    public function testRenderWithObject()
    {
        $tempura = new Tempura('Hello {{ user.username }}', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('Hello homer', $tempura->render());
    }

    public function testRenderWithObjectMethod()
    {
        $tempura = new Tempura('{{ user.username }} says: {{ user.says("hi") }}', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('homer says: hi', $tempura->render());
    }

}
