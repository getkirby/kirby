<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
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
