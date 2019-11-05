<?php

namespace Kirby\Text;

use Exception;
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
                    return $tag->value . '|' . $tag->a ;
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
            ]
        ];

        $this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
    }
}
