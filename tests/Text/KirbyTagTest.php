<?php

namespace Kirby\Text;

use PHPUnit\Framework\TestCase;

class KirbyTagTest extends TestCase
{

    public function setUp()
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a', 'b'],
                'html' => function () {
                    return 'test';
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

    public function tearDown()
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
        $this->assertEquals('attrA', $tag->attr('a'));
        $this->assertEquals('attrB', $tag->attr('b'));
    }

    public function testAttrFallback()
    {

        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA'
        ]);

        $this->assertNull($tag->b);
        $this->assertEquals('fallback', $tag->attr('b', 'fallback'));
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
