<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Dir::make($this->fixtures = __DIR__ . '/fixtures/Options');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testApi()
    {
        $source = $this->fixtures . '/test.json';

        $data = [
            'apple'     => 'Apple',
            'intel'     => 'Intel',
            'microsoft' => 'Microsoft'
        ];

        $expected = [
            [
                'text'  => 'Apple',
                'value' => 'apple'
            ],
            [
                'text'  => 'Intel',
                'value' => 'intel'
            ],
            [
                'text'  => 'Microsoft',
                'value' => 'microsoft'
            ],
        ];

        Data::write($source, $data);

        // with api
        $options = Options::api($source);
        $this->assertSame($expected, $options);

        // with factory
        $options = Options::factory('api', ['api' => $source]);
        $this->assertSame($expected, $options);
    }

    public function testApiFromArray()
    {
        $source = $this->fixtures . '/test.json';

        Data::write($source, [
            'Companies' => [
                ['name' => 'Apple'],
                ['name' => 'Intel'],
                ['name' => 'Microsoft'],
            ]
        ]);

        $options = Options::api([
            'url'   => $source,
            'fetch' => 'Companies',
            'text'  => '{{ item.name }}',
            'value' => '{{ item.name.slug }}'
        ]);

        $expected = [
            [
                'value' => 'apple',
                'text'  => 'Apple'
            ],
            [
                'value' => 'intel',
                'text'  => 'Intel'
            ],
            [
                'value' => 'microsoft',
                'text'  => 'Microsoft'
            ],
        ];

        $this->assertEquals($expected, $options);
    }

    public function testBlocks()
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'text' => json_encode([
                                [
                                    'id' => 'a',
                                    'content' => [
                                        'text' => 'Test Heading',
                                    ],
                                    'type' => 'heading',
                                ],
                                [
                                    'id' => 'b',
                                    'content' => [
                                        'text' => 'Test Text',
                                    ],
                                    'type' => 'text',
                                ]
                            ])
                        ]
                    ],
                ]
            ]
        ]);

        // with query
        $result = Options::query('site.find("a").text.toBlocks');

        $expected = [
            [
                'text'  => 'heading: a',
                'value' => 'a'
            ],
            [
                'text'  => 'text: b',
                'value' => 'b'
            ]
        ];

        $this->assertSame($expected, $result);

        // with array query
        $result = Options::query([
            'fetch' => 'site.find("a").text.toBlocks',
            'text'  => '{{ block.text }}',
            'value' => '{{ block.id }}',
        ]);

        $this->assertSame([
            [
                'text'  => 'Test Heading',
                'value' => 'a'
            ],
            [
                'text'  => 'Test Text',
                'value' => 'b'
            ]
        ], $result);
    }

    public function testPages()
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'title' => 'Page A'
                        ]
                    ],
                    [
                        'slug' => 'b'
                    ],
                ]
            ]
        ]);

        $expected = [
            [
                'text'  => 'Page A',
                'value' => 'a'
            ],
            [
                'text'  => 'b',
                'value' => 'b'
            ]
        ];

        // with query
        $result = Options::query('site.children');

        $this->assertSame($expected, $result);

        // with array query
        $result = Options::query([
            'fetch' => 'site.children',
            'text'  => '{{ page.title }}',
            'value' => '{{ page.title }}',
        ]);

        $this->assertSame([
            [
                'text'  => 'Page A',
                'value' => 'Page A'
            ],
            [
                'text'  => 'b',
                'value' => 'b'
            ]
        ], $result);

        // with factory
        $result = Options::factory('pages');

        $this->assertSame($expected, $result);

        // with invalid factory
        $result = Options::factory([]);

        $this->assertSame([], $result);
    }

    public function testUsers()
    {
        $app = new App([
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'name'  => 'Admin'
                ],
                [
                    'email' => 'editor@getkirby.com',
                ]
            ]
        ]);

        $result = Options::query('users');

        $expected = [
            [
                'text'  => 'Admin',
                'value' => 'admin@getkirby.com'
            ],
            [
                'text'  => 'editor@getkirby.com',
                'value' => 'editor@getkirby.com'
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testTranslated()
    {
        I18n::$locale = 'en';

        $options = Options::factory([
            'a' => ['en' => 'A (en)', 'de' => 'A (de)'],
            'b' => ['en' => 'B (en)', 'de' => 'B (de)']
        ]);

        $this->assertEquals('A (en)', $options[0]['text']);
        $this->assertEquals('B (en)', $options[1]['text']);

        I18n::$locale = 'de';

        $options = Options::factory([
            'a' => ['en' => 'A (en)', 'de' => 'A (de)'],
            'b' => ['en' => 'B (en)', 'de' => 'B (de)']
        ]);

        $this->assertEquals('A (de)', $options[0]['text']);
        $this->assertEquals('B (de)', $options[1]['text']);
    }

    public function testUntranslated()
    {
        I18n::$translations = [
            'en' => [
                'language' => 'Language'
            ],
            'de' => [
                'language' => 'Sprache'
            ]
        ];

        $options = Options::factory([
            'language' => 'language',
        ]);

        $this->assertEquals('language', $options[0]['text']);
    }
}
