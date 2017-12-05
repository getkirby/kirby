<?php

namespace Kirby\Cms;

class MockUser
{

    public function username()
    {
        return 'homer';
    }

    public function profiles()
    {
        return new Object([
            'twitter' => '@homer'
        ]);
    }

    public function says(...$message)
    {
        return implode(' ', $message);
    }

    public function age(int $years)
    {
        return $years;
    }

    public function isYello(bool $answer)
    {
        return $answer;
    }

    public function brainDump($dump)
    {
        return $dump;
    }

}


class QueryTest extends TestCase
{

    public function test0LevelArrayQuery()
    {
        $query = new Query('user', [
            'user' => 'homer'
        ]);

        $this->assertEquals('homer', $query->result());
    }

    public function test1LevelArrayQuery()
    {
        $query = new Query('user.username', [
            'user' => [
                'username' => 'homer'
            ]
        ]);

        $this->assertEquals('homer', $query->result());
    }

    public function test2LevelArrayQuery()
    {
        $query = new Query('user.profiles.twitter', [
            'user' => [
                'profiles' => [
                    'twitter' => '@homer'
                ]
            ]
        ]);

        $this->assertEquals('@homer', $query->result());
    }

    public function test1LevelObjectQuery()
    {
        $query = new Query('user.username', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('homer', $query->result());

        // 2-level
        $query = new Query('user.profiles.twitter', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('@homer', $query->result());

    }

    public function test2LevelObjectQuery()
    {
        $query = new Query('user.profiles.twitter', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('@homer', $query->result());
    }

    public function testObjectMethodWithSingleArgument()
    {
        $query = new Query('user.says("hello world")', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('hello world', $query->result());
    }

    public function testObjectMethodWithMultipleArguments()
    {
        $query = new Query('user.says("hello", "world")', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('hello world', $query->result());
    }

    public function testObjectMethodWithMultipleArgumentsAndComma()
    {
        $query = new Query('user.says("hello,", "world")', [
            'user' => new MockUser()
        ]);

        $this->assertEquals('hello, world', $query->result());
    }

    public function testObjectMethodWithInteger()
    {
        $query = new Query('user.age(12)', [
            'user' => new MockUser()
        ]);

        $this->assertEquals(12, $query->result());
    }

    public function testObjectMethodWithBoolean()
    {
        // true
        $query = new Query('user.isYello(true)', [
            'user' => new MockUser()
        ]);

        $this->assertTrue($query->result());

        // false
        $query = new Query('user.isYello(false)', [
            'user' => new MockUser()
        ]);

        $this->assertFalse($query->result());
    }

    public function testObjectMethodWithNull()
    {
        $query = new Query('user.brainDump(null)', [
            'user' => new MockUser()
        ]);

        $this->assertNull($query->result());
    }

}
