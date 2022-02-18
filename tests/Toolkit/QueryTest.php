<?php

namespace Kirby\Toolkit;

use stdClass;

/**
 * @covers Kirby\Toolkit\Query
 */
class QueryTest extends TestCase
{
    public function testWithEmptyQuery()
    {
        $query = new Query('', $data = ['foo' => 'bar']);
        $this->assertSame($data, $query->result());
    }

    public function testWithExactArrayMatch()
    {
        $query = new Query('user', [
            'user' => 'homer'
        ]);
        $this->assertSame('homer', $query->result());

        $query = new Query('user.username', [
            'user.username' => 'homer'
        ]);
        $this->assertSame('homer', $query->result());
    }

    public function testWithContainsNumericParts()
    {
        $query = new Query('user0.profiles1.twitter', [
            'user0' => [
                'profiles1' => [
                    'twitter' => '@homer'
                ]
            ]
        ]);

        $this->assertSame('@homer', $query->result());
    }

    public function testWithArray1Level()
    {
        $query = new Query('user.username', [
            'user' => [
                'username' => 'homer'
            ]
        ]);

        $this->assertSame('homer', $query->result());
    }

    public function testWithArrayNumeric()
    {
        $query = new Query('user.0', [
            'user' => [
                'homer',
                'marge'
            ]
        ]);

        $this->assertSame('homer', $query->result());

        $query = new Query('user.1', [
            'user' => [
                'homer',
                'marge'
            ]
        ]);

        $this->assertSame('marge', $query->result());
    }

    public function testWithArray2Level()
    {
        $query = new Query('user.profiles.twitter', [
            'user' => [
                'profiles' => [
                    'twitter' => '@homer'
                ]
            ]
        ]);

        $this->assertSame('@homer', $query->result());
    }

    /**
     * @dataProvider scalarProvider
     */
    public function testWithArrayScalarValue($scalar)
    {
        $query = new Query('value', [
            'value' => $scalar
        ]);

        $this->assertSame($scalar, $query->result());
    }

    /**
     * @dataProvider scalarProvider
     */
    public function testWithArrayScalarValue2Level($scalar)
    {
        $query = new Query('parent.value', [
            'parent' => [
                'value' => $scalar
            ]
        ]);

        $this->assertSame($scalar, $query->result());
    }

    /**
     * @dataProvider scalarProvider
     */
    public function testWithArrayScalarValueError($scalar, $type)
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to method/property method on ' . $type);

        $query = new Query('value.method', [
            'value' => $scalar
        ]);

        $query->result();
    }

    public function scalarProvider()
    {
        return [
            ['test', 'string'],
            [1, 'integer'],
            [1.1, 'float'],
            [true, 'boolean'],
            [false, 'boolean']
        ];
    }

    public function testWithArrayNullValue()
    {
        $query = new Query('value', [
            'value' => null
        ]);

        $this->assertNull($query->result());
    }

    public function testWithArrayNullValueError()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to method/property method on null');

        $query = new Query('value.method', [
            'value' => null
        ]);

        $this->assertNull($query->result());
    }

    public function testWithArrayCallClosure()
    {
        $query = new Query('closure("test")', [
            'closure' => function ($arg) {
                return strtoupper($arg);
            }
        ]);

        $this->assertSame('TEST', $query->result());
    }

    public function testWithArrayCallError()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Cannot access array element user with arguments');

        $query = new Query('user("test")', [
            'user' => new QueryTestUser()
        ]);

        $query->result();
    }

    public function testWithArrayMissingKey1()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to non-existing property user on array');

        $query = new Query('user', []);
        $query->result();
    }

    public function testWithArrayMissingKey2()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to non-existing property user on array');

        $query = new Query('user.username', []);
        $query->result();
    }

    public function testWithObject1Level()
    {
        $query = new Query('user.username', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('homer', $query->result());
    }

    public function testWithObject2Level()
    {
        $query = new Query('user.profiles.twitter', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('@homer', $query->result());
    }

    public function testWithObjectProperty()
    {
        $obj = new stdClass();
        $obj->test = 'testtest';
        $query = new Query('obj.test', compact('obj'));

        $this->assertSame('testtest', $query->result());
    }

    public function testWithObjectPropertyCallError()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to non-existing method test on object');

        $obj = new stdClass();
        $obj->test = 'testtest';
        $query = new Query('obj.test(123)', compact('obj'));

        $query->result();
    }

    public function testWithObjectMethodWithInteger()
    {
        $query = new Query('user.age(12)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame(12, $query->result());
    }

    public function testWithObjectMethodWithBoolean()
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

    public function testWithObjectMethodWithNull()
    {
        $query = new Query('user.brainDump(null)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertNull($query->result());
    }

    public function testWithObjectMethodWithString()
    {
        // double quotes
        $query = new Query('user.says("hello world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello world', $query->result());

        // single quotes
        $query = new Query("user.says('hello world')", [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello world', $query->result());
    }

    public function testWithObjectMethodWithEmptyString()
    {
        // double quotes
        $query = new Query('user.says("")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('', $query->result());

        // single quotes
        $query = new Query("user.says('')", [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('', $query->result());
    }

    public function testWithObjectMethodWithStringEscape()
    {
        // double quotes
        $query = new Query('user.says("hello \" world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello " world', $query->result());

        // single quotes
        $query = new Query("user.says('hello \' world')", [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame("hello ' world", $query->result());
    }

    public function testWithObjectMethodWithMultipleArguments()
    {
        $query = new Query('user.says("hello", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello : world', $query->result());

        // with escaping
        $query = new Query('user.says("hello\"", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello" : world', $query->result());

        // with mixed quotes
        $query = new Query('user.says(\'hello\\\'\', "world\"")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello\' : world"', $query->result());
    }

    public function testWithObjectMethodWithMultipleArgumentsAndComma()
    {
        $query = new Query('user.says("hello,", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello, : world', $query->result());

        // with escaping
        $query = new Query('user.says("hello,\"", "world")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello," : world', $query->result());
    }

    public function testWithObjectMethodWithMultipleArgumentsAndDot()
    {
        $query = new Query('user.says("I like", "love.jpg")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('I like : love.jpg', $query->result());

        // with escaping
        $query = new Query('user.says("I \" like", "love.\"jpg")', [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('I " like : love."jpg', $query->result());
    }

    public function testWithObjectMethodWithTrickyCharacters()
    {
        $query = new Query("user.likes(['(', ',', ']', '[', ')']).self.brainDump('hello')", [
            'user' => new QueryTestUser()
        ]);

        $this->assertSame('hello', $query->result());
    }

    public function testWithObjectMethodWithArray()
    {
        $query = new Query('user.self.check("gin", "tonic", ["gin", "tonic", "cucumber"])', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());
    }

    public function testWithObjectMethodWithObjectMethodAsParameter()
    {
        $query = new Query('user.self.check("gin", "tonic", user.drink)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());
    }

    public function testWithNestedMethodCall()
    {
        $query = new Query('user.check("gin", "tonic", user.array("gin", "tonic").args)', [
            'user' => new QueryTestUser()
        ]);

        $this->assertTrue($query->result());
    }

    public function testWithObjectMethodWithObjectMethodAsParameterAndMoreLevels()
    {
        $query = new Query("user.likes([',']).likes(user.brainDump(['(', ',', ']', ')', '['])).self", [
            'user' => $user = new QueryTestUser()
        ]);

        $this->assertSame($user, $query->result());
    }

    public function testWithObjectMissingMethod1()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to non-existing method/property username on object');

        $query = new Query('user.username', ['user' => new stdClass()]);
        $query->result();
    }

    public function testWithObjectMissingMethod2()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Access to non-existing method username on object');

        $query = new Query('user.username(12)', ['user' => new stdClass()]);
        $query->result();
    }
}
