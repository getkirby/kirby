<?php

namespace Kirby\Cms;

class SearchTest extends TestCase
{
    public function testCollection()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'homer',
                'content' => ['name' => 'Homer']
            ],
            [
                'slug'    => 'marge',
                'content' => ['name' => 'Marge']
            ],
            [
                'slug'    => 'maggie',
                'content' => ['name' => 'Maggie']
            ],
            [
                'slug'    => 'lisa',
                'content' => ['name' => 'Lisa']
            ]
        ]);

        $search = Search::collection($collection, 'ma');
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'Ho');
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'm', ['minlength' => 1]);
        $this->assertCount(3, $search);
    }


    public function testIgnoreFieldCase()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'homer',
                'content' => ['firstname' => 'Homer']
            ],
            [
                'slug'    => 'marge',
                'content' => ['firstname' => 'Marge']
            ],
            [
                'slug'    => 'maggie',
                'content' => ['firstname' => 'Maggie']
            ],
            [
                'slug'    => 'lisa',
                'content' => ['firstname' => 'Lisa']
            ],
            [
                'slug'    => 'snowball',
                'content' => ['firstname' => 'Šnowball']
            ]
        ]);

        $search = Search::collection($collection, 'ma', ['fields' => ['FirstName']]);
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'Ho', ['fields' => ['FirstName']]);
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'm', ['minlength' => 1, 'fields' => ['FirstName']]);
        $this->assertCount(3, $search);
    }

    public function testIgnoreCaseI18n()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'santa',
                'content' => ['full' => 'Santa\'s Little Helper']
            ],
            [
                'slug'    => 'snowball',
                'content' => ['full' => 'Šnowball']
            ],
            [
                'slug'    => 'garfield',
                'content' => ['full' => 'Garfield']
            ]
        ]);

        $search = Search::collection($collection, 's', ['minlength' => 1]);
        $this->assertCount(2, $search);
        $search = Search::collection($collection, 'S', ['minlength' => 1]);
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'š', ['minlength' => 1]);
        $this->assertCount(1, $search);
        $search = Search::collection($collection, 'Š', ['minlength' => 1]);
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'g', ['minlength' => 1]);
        $this->assertCount(1, $search);
        $search = Search::collection($collection, 'G', ['minlength' => 1]);
        $this->assertCount(1, $search);
    }

    public function app(): App
    {
        return new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'categories',
                        'files' => [
                            ['filename' => 'phone.jpg'],
                            ['filename' => 'cell-phone.jpg'],
                            ['filename' => 'computer.jpg']
                        ]
                    ],
                    [
                        'slug'  => 'products',
                        'files' => [
                            ['filename' => 'apple.jpg'],
                            ['filename' => 'samsung.jpg']
                        ]
                    ],
                    [
                        'slug' => 'contact'
                    ]
                ],
                'files' => [
                    ['filename' => 'website.jpg']
                ]
            ],
            'users' => [
                ['email' => 'admin@getkirby.com'],
                ['email' => 'editor@getkirby.com'],
                ['email' => 'user1@getkirby.com'],
                ['email' => 'user2@getkirby.com'],
                ['email' => 'user3@getkirby.com']
            ],
        ]);
    }

    public function testFiles()
    {
        $this->assertSame(5, $this->app()->site()->index()->files()->count());
        $this->assertInstanceOf(Files::class, $files = Search::files('phone'));
        $this->assertSame(2, $files->count());
    }

    public function testPages()
    {
        $this->assertSame(3, $this->app()->site()->index()->count());
        $this->assertInstanceOf(Pages::class, $pages = Search::pages('products'));
        $this->assertSame(1, $pages->count());
    }

    public function testUsers()
    {
        $this->assertSame(5, $this->app()->users()->count());
        $this->assertInstanceOf(Users::class, $users = Search::users('user'));
        $this->assertSame(3, $users->count());
    }
}
