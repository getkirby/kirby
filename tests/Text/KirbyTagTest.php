<?php

namespace Kirby\Text;

use Kirby\Cms\App;
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

    public function test__call()
    {
        $attr = [
            'a' => 'attrA',
            'b' => 'attrB'
        ];

        $data = [
            'a' => 'dataA',
            'c' => 'dataC'
        ];

        $tag = new KirbyTag('test', 'test value', $attr, $data);

        $this->assertSame('dataA', $tag->a());
        $this->assertSame('attrB', $tag->b());
        $this->assertSame('dataC', $tag->c());
    }

    public function test__callStatic()
    {
        $attr = [
            'a' => 'attrA',
            'b' => 'attrB'
        ];

        $result = KirbyTag::test('test value', $attr);

        $this->assertSame('test: test value-attrA-attrB', $result);
    }

    public function testAttr()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);

        // class properties
        $this->assertSame('attrA', $tag->a);
        $this->assertSame('attrB', $tag->b);

        // attr helper
        $this->assertSame('attrA', $tag->attr('a', 'fallback'));
        $this->assertSame('attrB', $tag->attr('b', 'fallback'));
    }

    public function testAttrFallback()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA'
        ]);

        $this->assertNull($tag->b);
        $this->assertSame('fallback', $tag->attr('b', 'fallback'));
    }

    public function testFactory()
    {
        $attr = [
            'a' => 'attrA',
            'b' => 'attrB'
        ];

        $result = KirbyTag::factory('test', 'test value', $attr);

        $this->assertSame('test: test value-attrA-attrB', $result);
    }

    public function testOption()
    {
        $attr = [
            'a' => 'attrA',
            'b' => 'attrB'
        ];

        $data = [
            'a' => 'dataA',
            'b' => 'dataB'
        ];

        $options = [
            'a' => 'optionA',
            'b' => 'optionB'
        ];

        $tag = new KirbyTag('test', 'test value', $attr, $data, $options);

        $this->assertSame('optionA', $tag->option('a'));
        $this->assertSame('optionB', $tag->option('b'));
        $this->assertSame('optionC', $tag->option('c', 'optionC'));
    }

    public function testWithParent()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg'
                            ],
                            [
                                'filename' => 'b.jpg'
                            ],
                            [
                                'filename' => 'c.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('a');
        $image = $page->image('b.jpg');
        $expected = '<figure><img alt="" src="/media/pages/a/' . $image->mediaHash() . '/b.jpg"></figure>';

        $this->assertSame($expected, $app->kirbytag('image', 'b.jpg', [], [
            'parent' => $page,
        ]));
    }

    public function testParse()
    {
        $tag = KirbyTag::parse('(test: test value)', ['some' => 'data'], ['some' => 'options']);
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame(['some' => 'data'], $tag->data);
        $this->assertSame(['some' => 'options'], $tag->options);
        $this->assertSame([], $tag->attrs);

        $tag = KirbyTag::parse('test: test value');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([], $tag->attrs);

        $tag = KirbyTag::parse('test:');
        $this->assertSame('test', $tag->type);
        $this->assertSame('', $tag->value);
        $this->assertSame([], $tag->attrs);

        $tag = KirbyTag::parse('test: ');
        $this->assertSame('test', $tag->type);
        $this->assertSame('', $tag->value);
        $this->assertSame([], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test:test value a:attrA b:attrB');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b:');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => ''
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: ');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => ''
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB ');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA c: attrC b: attrB');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA c: attrC',
            'b' => 'attrB'
        ], $tag->attrs);

        $tag = KirbyTag::parse('test: test value a: attrA b: attrB c: attrC');
        $this->assertSame('test', $tag->type);
        $this->assertSame('test value', $tag->value);
        $this->assertSame([
            'a' => 'attrA',
            'b' => 'attrB c: attrC'
        ], $tag->attrs);
    }

    public function testParseInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Undefined tag type: invalid');

        KirbyTag::parse('invalid: test value a: attrA b: attrB');
    }

    public function testRender()
    {
        $tag = new KirbyTag('test', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $this->assertSame('test: test value-attrA-attrB', $tag->render());

        $tag = new KirbyTag('test', '', [
            'a' => 'attrA'
        ]);
        $this->assertSame('test: -attrA-', $tag->render());
    }

    public function testRenderNoHtml()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Invalid tag render function in tag: noHtml');

        $tag = new KirbyTag('noHtml', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $tag->render();
    }

    public function testRenderInvalidHtml()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('Invalid tag render function in tag: invalidHtml');

        $tag = new KirbyTag('invalidHtml', 'test value', [
            'a' => 'attrA',
            'b' => 'attrB'
        ]);
        $tag->render();
    }

    public function testType()
    {
        $tag = new KirbyTag('test', 'test value');
        $this->assertSame('test', $tag->type());
    }
}
