<?php

namespace Kirby\Cms;

use Kirby\Data\Yaml;
use PHPUnit\Framework\TestCase;

class BlocksTest extends TestCase
{
    protected $page;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
        ]);

        $this->page = new Page(['slug' => 'test']);
    }

    public function testFactoryFromLayouts()
    {
        $layouts = [
            [
                'columns' => [
                    [
                        'blocks' => [
                            [
                                'type' => 'heading'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'columns' => [
                    [
                        'blocks' => [
                            [
                                'type' => 'text'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $blocks = Blocks::factory($layouts);

        $this->assertCount(2, $blocks);
        $this->assertSame('heading', $blocks->first()->type());
        $this->assertSame('text', $blocks->last()->type());
    }

    public function testFactoryFromBuilderWithColumns()
    {
        $builder = [
            [
                '_key' => 'heading',
                'columns' => 1,
            ],
            [
                '_key' => 'text',
                'columns' => 2,
            ]
        ];

        $blocks = Blocks::factory($builder);

        $this->assertCount(2, $blocks);
        $this->assertSame('heading', $blocks->first()->type());
        $this->assertSame('text', $blocks->last()->type());
    }

    public function testHasType()
    {
        $input = [
            [
                'type' => 'heading'
            ]
        ];

        $blocks = Blocks::factory($input);

        $this->assertTrue($blocks->hasType('heading'));
        $this->assertFalse($blocks->hasType('code'));
    }

    public function testParseJson()
    {
        $input = [
            [
                'type' => 'heading'
            ]
        ];

        $result = Blocks::parse(json_encode($input));
        $this->assertSame($input, $result);
    }

    public function testParseYaml()
    {
        $input = [
            [
                'type' => 'heading'
            ]
        ];

        $result = Blocks::parse(Yaml::encode($input));
        $this->assertSame($input, $result);
    }

    public function testParseHtml()
    {
        $input = '<h1>Test</h1>';
        $expected = [
            [
                'content' => [
                    'level' => 'h1',
                    'text' => 'Test'
                ],
                'type' => 'heading',
            ]
        ];

        $result = Blocks::parse($input);
        $this->assertSame($expected, $result);
    }

    public function testParseEmpty()
    {
        $result = Blocks::parse(null);
        $this->assertSame([], $result);

        $result = Blocks::parse('');
        $this->assertSame([], $result);
    }

    public function testParseString()
    {
        $expected = [
            [
                'content' => [
                    'text' => '<p>This is test string</p>'
                ],
                'type' => 'text',
            ]
        ];

        $result = Blocks::parse('This is test string');
        $this->assertSame($expected, $result);
    }

    public function testParsePageObject()
    {
        $expected = [
            [
                'content' => [
                    'text' => '<p>test</p>'
                ],
                'type' => 'text',
            ]
        ];

        $result = Blocks::parse($this->page);
        $this->assertSame($expected, $result);
    }

    public function testToHtml()
    {
        $blocks = Blocks::factory([
            [
                'content' => [
                    'text' => 'Hello world'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => 'Nice blocks'
                ],
                'type' => 'text'
            ],
        ]);

        $expected = "<h2>Hello world</h2>\nNice blocks";

        $this->assertSame($expected, $blocks->toHtml());
    }

    public function testToHtmlWithCustomSnippets()
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
                'snippets' => __DIR__ . '/fixtures/snippets'
            ],
        ]);

        $blocks = Blocks::factory([
            [
                'content' => [
                    'text' => 'Hello world'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => 'Nice blocks'
                ],
                'type' => 'text'
            ],
        ]);

        $expected = "<h1 class=\"custom-heading\">Hello world</h1>\n<p class=\"custom-text\">Nice blocks</p>\n";

        $this->assertSame($expected, $blocks->toHtml());
    }

    public function testExcerpt()
    {
        $blocks = Blocks::factory([
            [
                'content' => [
                    'text' => 'Hello world'
                ],
                'type' => 'heading'
            ],
            [
                'content' => [
                    'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
                ],
                'type' => 'text'
            ],
        ]);

        $expected = "<h2>Hello world</h2>\nLorem ipsum dolor sit amet, consectetur adipiscing elit.";

        $this->assertSame($expected, $blocks->toHtml());
        $this->assertSame('Hello world Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $blocks->excerpt());
        $this->assertSame('Hello world Lorem ipsum dolor sit amet, …', $blocks->excerpt(50));
        $this->assertSame($expected, (string)$blocks);
    }
}
