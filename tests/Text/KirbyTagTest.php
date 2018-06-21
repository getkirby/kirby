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

}
