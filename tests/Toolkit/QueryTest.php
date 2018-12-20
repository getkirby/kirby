<?php

namespace Kirby\Toolkit;

class QueryTest extends TestCase
{
    public function testWithMissingData()
    {
        // 1-level
        $query = new Query('user', []);

        $this->assertEquals(null, $query->result());

        // 2-level
        $query = new Query('user.username', []);

        $this->assertEquals(null, $query->result());
    }

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
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('homer', $query->result());

        // 2-level
        $query = new Query('user.profiles.twitter', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('@homer', $query->result());
    }

    public function test2LevelObjectQuery()
    {
        $query = new Query('user.profiles.twitter', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('@homer', $query->result());
    }

    public function testObjectMethodWithSingleArgument()
    {
        $query = new Query('user.says("hello world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('hello world', $query->result());
    }

    public function testObjectMethodWithMultipleArguments()
    {
        $query = new Query('user.says("hello", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('hello world', $query->result());
    }

    public function testObjectMethodWithMultipleArgumentsAndComma()
    {
        $query = new Query('user.says("hello,", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('hello, world', $query->result());
    }

    public function testObjectMethodWithInteger()
    {
        $query = new Query('user.age(12)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals(12, $query->result());
    }

    public function testObjectMethodWithBoolean()
    {
        // true
        $query = new Query('user.isYello(true)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());

        // false
        $query = new Query('user.isYello(false)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertFalse($query->result());
    }

    public function testObjectMethodWithNull()
    {
        $query = new Query('user.brainDump(null)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertNull($query->result());
    }
}
