<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Find
 */
class FindTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    /**
     * @covers ::file
     */
    public function testFileForPage(): void
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            ['filename' => 'a.jpg']
                        ],
                        'children' => [
                            [
                                'slug' => 'aa',
                                'files' => [
                                    ['filename' => 'aa.jpg']
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('a.jpg', Find::file('pages/a', 'a.jpg')->filename());
        $this->assertEquals('aa.jpg', Find::file('pages/a+aa', 'aa.jpg')->filename());
    }

    /**
     * @covers ::file
     */
    public function testFileForSite(): void
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('test.jpg', Find::file('site', 'test.jpg')->filename());
    }

    /**
     * @covers ::file
     */
    public function testFileForUser(): void
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'test.jpg']
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('test.jpg', Find::file('users/test@getkirby.com', 'test.jpg')->filename());
    }

    /**
     * @covers ::file
     */
    public function testFileNotFound()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The file "nope.jpg" cannot be found');

        Find::file('site', 'nope.jpg');
    }

    /**
     * @covers ::file
     */
    public function testFileNotReadable()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    [
                        'filename' => 'protected.jpg',
                        'template' => 'protected'
                    ]
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The file "protected.jpg" cannot be found');

        Find::file('site', 'protected.jpg');
    }

    /**
     * @covers ::language
     */
    public function testLanguage()
    {
        $app = $this->app->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch',
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $this->assertSame('en', Find::language('en')->code());
        $this->assertSame('de', Find::language('de')->code());
    }

    /**
     * @covers ::language
     */
    public function testLanguageNotFound()
    {
        $this->app->impersonate('kirby');

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The language could not be found');

        Find::language('en');
    }

    /**
     * @covers ::page
     */
    public function testPage()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'aa'
                            ],
                        ],
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $a  = $app->page('a');
        $aa = $app->page('a/aa');

        $this->assertEquals($a, Find::page('a'));
        $this->assertEquals($aa, Find::page('a+aa'));
    }

    /**
     * @covers ::page
     */
    public function testPageNotReadable()
    {
        $app = $this->app->clone([
            'blueprints' => [
                'pages/protected' => [
                    'options' => [
                        'read' => false
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'a',
                        'template' => 'protected'
                    ]
                ]
            ]
        ]);

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "a" cannot be found');

        Find::page('a');
    }

    /**
     * @covers ::page
     */
    public function testPageNotFound()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "does-not-exist" cannot be found');

        Find::page('does-not-exist');
    }

    /**
     * @covers ::parent
     */
    public function testParent()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'children' => [
                            [
                                'slug' => 'aa'
                            ],
                        ],
                        'files' => [
                            ['filename' => 'a-regular-file.jpg']
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'sitefile.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'current@getkirby.com',
                ],
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'userfile.jpg']
                    ]
                ]
            ],
            'options' => [
                'api' => [
                    'allowImpersonation' => true,
                ]
            ]
        ]);

        $app->impersonate('current@getkirby.com');

        $this->assertInstanceOf(User::class, Find::parent('account'));
        $this->assertInstanceOf(User::class, Find::parent('users/test@getkirby.com'));
        $this->assertInstanceOf(Site::class, Find::parent('site'));
        $this->assertInstanceOf(Site::class, Find::parent('/site'));
        $this->assertInstanceOf(Page::class, Find::parent('pages/a+aa'));
        $this->assertInstanceOf(Page::class, Find::parent('pages/a aa'));
        $this->assertInstanceOf(File::class, Find::parent('site/files/sitefile.jpg'));
        $this->assertInstanceOf(File::class, Find::parent('pages/a/files/a-regular-file.jpg'));
        $this->assertInstanceOf(File::class, Find::parent('users/test@getkirby.com/files/userfile.jpg'));
    }

    /**
     * @covers ::parent
     */
    public function testParentWithInvalidModelType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid model type: something');
        $this->assertNull(Find::parent('something/something'));
    }

    /**
     * @covers ::parent
     */
    public function testParentNotFound()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The page "does-not-exist" cannot be found');
        $this->assertNull(Find::parent('pages/does-not-exist'));
    }

    /**
     * @covers ::parent
     */
    public function testParentUndefined()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user cannot be found');
        $this->assertNull(Find::parent('users/does-not-exist'));
    }

    /**
     * @covers ::user
     */
    public function testUser()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('test@getkirby.com', Find::user('test@getkirby.com')->email());
    }

    /**
     * @covers ::user
     */
    public function testUserWithAuthentication()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                ]
            ],
            'options' => [
                'api' => [
                    'allowImpersonation' => true,
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');
        $this->assertEquals('test@getkirby.com', Find::user()->email());
    }

    /**
     * @covers ::user
     */
    public function testUserWithoutAllowedImpersonation()
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user cannot be found');

        Find::user()->email();
    }

    /**
     * @covers ::user
     */
    public function testUserForAccountArea()
    {
        $app = $this->app->clone([
            'options' => [
                'api' => [
                    'allowImpersonation' => true
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $app->impersonate('test@getkirby.com');
        $this->assertEquals('test@getkirby.com', Find::user('account')->email());
    }

    /**
     * @covers ::user
     */
    public function testUserNotFound()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');

        Find::user('nope@getkirby.com');
    }
}
