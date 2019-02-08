<?php

namespace Kirby\Text;

use PHPUnit\Framework\TestCase;

class KirbyTagTest extends TestCase
{
    public function setUp(): void
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a', 'b'],
                'html' => function ($tag) {
                    return 'test: ' . $tag->value . '-' . $tag->a . '-' . $tag->b;
                }
            ],
            'noHtml' => [
                'attr' => ['a', 'b']
            ],
            'invalidHtml' => [
                'attr' => ['a', 'b'],
                'html' => 'some string'
            ]
        ];
    }

    public function tearDown(): void
    {
        KirbyTag::$types = [];
    }

    public function testAttr()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);

        // class properties
        $this->assertEquals('attrA', $tag->a);
        $this->assertEquals('attrB', $tag->b);

        // attr helper
        $this->assertEquals('attrA', $tag->attr('a', 'fallback'));
        $this->assertEquals('attrB', $tag->attr('b', 'fallback'));
    }

    public function testAttrFallback()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA'
        ]);

        $this->assertNull($tag->b);
        $this->assertEquals('fallback', $tag->attr('b', 'fallback'));
    }

    public function testParse()
    {
        $tag = KirbyTag::parse('(test: test value)', ['some' => 'data'], ['some' => 'options']);
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals(['some' => 'data'], $tag->data);
        $this->assertEquals(['some' => 'options'], $tag->options);
        $this->assertEquals([], $tag->attrs);

        $tag = KirbyTag::parse('test: test value');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([], $tag->attrs);

        $tag = KirbyTag::parse('test:');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('', $tag->value);
        $this->assertEquals([], $tag->attrs);

        $tag = KirbyTag::parse('test: ');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('', $tag->value);
        $this->assertEquals([], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test:test value a:attrA b:attrB');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b:');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => ''
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: ');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => ''
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB ');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA c: attrC b: attrB');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA c: attrC',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB c: attrC');
        $this->assertEquals('test', $tag->type);
        $this->assertEquals('test value', $tag->value);
        $this->assertEquals([
            'a' => 'attrA',
            'b' => 'attrB c: attrC'
        ], $tag->attrs);
    }

    /**
     * @expectedException        Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Undefined tag type: invalid
     */
    public function testParseInvalid()
    {
        KirbyTag::parse('invalid: test value a: attrA b: attrB');
    }

    public function testRender()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $this->assertEquals('test: test value-attrA-attrB', $tag->render());

        $tag = new KirbyTag('test', '', [
            'a' => 'attrA'
        ]);
        $this->assertEquals('test: -attrA-', $tag->render());
    }

    /**
     * @expectedException        Kirby\Exception\BadMethodCallException
     * @expectedExceptionMessage Invalid tag render function in tag: noHtml
     */
    public function testRenderNoHtml()
    {
        $tag = new KirbyTag('noHtml', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $tag->render();
    }

    /**
     * @expectedException        Kirby\Exception\BadMethodCallException
     * @expectedExceptionMessage Invalid tag render function in tag: invalidHtml
     */
    public function testRenderInvalidHtml()
    {
        $tag = new KirbyTag('invalidHtml', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $tag->render();
    }
}
