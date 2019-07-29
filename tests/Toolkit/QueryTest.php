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

    public function testWithEmptyQuery()
    {
        $query = new Query('', $data = ['foo' => 'bar']);
        $this->assertEquals($data, $query->result());
    }

    public function testWithDottedData()
    {
        $query = new Query('user.username', [
            'user.username' => 'homer'
        ]);

        $this->assertEquals('homer', $query->result());
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

    public function scalarProvider()
    {
        return [
            ['test'],
            [1],
            [1.1],
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider scalarProvider
     */
    public function test1LevelScalarQuery($scalar)
    {
        $query = new Query('value', [
            'value' => $scalar
        ]);

        $this->assertEquals($scalar, $query->result());
    }

    /**
     * @dataProvider scalarProvider
     */
    public function test2LevelScalarQuery($scalar)
    {
        $query = new Query('parent.value', [
            'parent' => [
                'value' => $scalar
            ]
        ]);

        $this->assertEquals($scalar, $query->result());
    }

    public function testNullValueQuery()
    {
        $query = new Query('value', [
            'value' => null
        ]);

        $this->assertEquals(null, $query->result());
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

    public function testObjectMethodWithMultipleArgumentsAndDot()
    {
        $query = new Query('user.says("I like", "love.jpg")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('I like love.jpg', $query->result());
    }

    public function testObjectMethodWithTrickyCharacters()
    {
        $query = new Query("user.likes(['(', ',', ']', '[']).self.brainDump('hello')", [
            'user' => new QueryTestUser()
        ]);

        $this->assertEquals('hello', $query->result());
    }

    public function testObjectMethodWithArray()
    {
        $query = new Query('user.self.check("gin", "tonic", ["gin", "tonic", "cucumber"])', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());
    }

    public function testObjectMethodWithObjectMethodAsParameter()
    {
        $query = new Query('user.self.check("gin", "tonic", user.drink)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());
    }

    public function testObjectMethodWithObjectMethodAsParameterAndMoreLevels()
    {
        $query = new Query("user.likes([',']).likes(user.brainDump(['(', ',', ']', '['])).self", [
            'user' => $user = new QueryTestUser()
        ]);

        $this->assertEquals($user, $query->result());
    }
}
