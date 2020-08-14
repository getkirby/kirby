<?php

namespace Kirby\Cms;

class SearchTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
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
                        'slug'  => 'contact'
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
        $files = Search::files('phone');

        $this->assertInstanceOf(Files::class, $files);
        $this->assertSame(5, $this->app->site()->index()->files()->count());
        $this->assertSame(2, $files->count());
    }

    public function testPages()
    {
        $pages = Search::pages('products');

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertSame(3, $this->app->site()->index()->count());
        $this->assertSame(1, $pages->count());
    }

    public function testUsers()
    {
        $users = Search::users('user');

        $this->assertInstanceOf(Users::class, $users);
        $this->assertSame(5, $this->app->users()->count());
        $this->assertSame(3, $users->count());
    }
}
