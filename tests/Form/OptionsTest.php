<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
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

        Data::write($source, $data);

        $options = Options::api($source);

        $this->assertEquals($expected, $options);
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

        $result = Options::query('site.children');

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

        $this->assertEquals($expected, $result);
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
}
