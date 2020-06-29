<?php

namespace Kirby\Text;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class KirbyTagsTest extends TestCase
{
    public function testParse()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    return 'test';
                }
            ]
        ];

        $this->assertSame('test', KirbyTags::parse('(test: foo)'));
        $this->assertSame('test', KirbyTags::parse('(Test: foo)'));
        $this->assertSame('test', KirbyTags::parse('(TEST: foo)'));
        $this->assertSame('test', KirbyTags::parse('(tEsT: foo)'));
    }

    public function testParseWithValue()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function ($tag) {
                    return $tag->value;
                }
            ]
        ];

        $this->assertSame('foo', KirbyTags::parse('(test: foo)'));
        $this->assertSame('foo', KirbyTags::parse('(Test: foo)'));
        $this->assertSame('foo', KirbyTags::parse('(TEST: foo)'));
    }

    public function testParseWithAttribute()
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a'],
                'html' => function ($tag) {
                    return $tag->value . '|' . $tag->a;
                }
            ]
        ];

        $this->assertSame('foo|bar', KirbyTags::parse('(test: foo a: bar)'));
        $this->assertSame('foo|bar', KirbyTags::parse('(Test: foo A: bar)'));
        $this->assertSame('foo|bar', KirbyTags::parse('(TEST: foo a: bar)'));
    }

    public function testParseWithException()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    throw new Exception('Just for fun');
                }
            ],
            'invalidargument' => [
                'html' => function () {
                    throw new InvalidArgumentException('Just for fun');
                }
            ],
            'undefined' => [
                'html' => function () {
                    throw new InvalidArgumentException('Undefined tag type: undefined');
                }
            ]
        ];

        $this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
        $this->assertSame('(invalidargument: foo)', KirbyTags::parse('(invalidargument: foo)'));
        $this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)'));
    }

    public function testParseWithExceptionDebug1()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Just for fun');

        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    throw new Exception('Just for fun');
                }
            ]
        ];

        KirbyTags::parse('(test: foo)', [], ['debug' => true]);
    }

    public function testParseWithExceptionDebug2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Just for fun');

        KirbyTag::$types = [
            'invalidargument' => [
                'html' => function () {
                    throw new InvalidArgumentException('Just for fun');
                }
            ]
        ];

        KirbyTags::parse('(invalidargument: foo)', [], ['debug' => true]);
    }

    public function testParseWithExceptionDebug3()
    {
        KirbyTag::$types = [
            'undefined' => [
                'html' => function () {
                    throw new InvalidArgumentException('Undefined tag type: undefined');
                }
            ]
        ];

        $this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)', [], ['debug' => true]));
    }

    public function testParseWithBrackets()
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a'],
                'html' => function ($tag) {
                    $value = $tag->value;

                    if (empty($tag->a) === false) {
                        $value .= ' - ' . $tag->a;
                    }

                    return $value;
                }
            ]
        ];

        $this->assertSame('foo(bar)', KirbyTags::parse('(test: foo(bar))'));
        $this->assertSame('foo(bar) - hello(world)', KirbyTags::parse('(test: foo(bar) a: hello(world))'));
        $this->assertSame('foo(bar) hello', KirbyTags::parse('(test: foo(bar) hello)'));
        $this->assertSame('foo(bar hello(world))', KirbyTags::parse('(test: foo(bar hello(world)))'));
        $this->assertSame('foo - (bar)', KirbyTags::parse('(test: foo a: (bar))'));
        $this->assertSame('(bar)', KirbyTags::parse('(test: (bar))'));
        // will not parse if brackets don't match
        $this->assertSame('(test: foo (bar)', KirbyTags::parse('(test: foo (bar)'));
    }
}
