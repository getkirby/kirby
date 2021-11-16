<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\Http\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Home
 */
class HomeTest extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        Blueprint::$loaded = [];

        // fake a valid server
        $_SERVER['SERVER_SOFTWARE'] = 'php';

        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ],
            'options' => [
                'panel' => [
                    'install' => true
                ]
            ],
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        $this->app->session()->destroy();
        unset($_SERVER['SERVER_SOFTWARE']);
        Dir::remove($this->tmp);
    }

    /**
     * @covers ::alternative
     */
    public function testAlternative()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertSame('/panel/site', Home::alternative($user));
    }

    /**
     * @covers ::alternative
     */
    public function testAlternativeWithLimitedAccess()
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'site' => false
                        ]
                    ]
                ],
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertSame('/panel/users', Home::alternative($user));
    }

    /**
     * @covers ::alternative
     */
    public function testAlternativeWithOnlyAccessToAccounts()
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'site'   => false,
                            'users'  => false,
                            'system' => false
                        ]
                    ]
                ],
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertSame('/panel/account', Home::alternative($user));
    }

    /**
     * @covers ::alternative
     */
    public function testAlternativeWithoutPanelAccess()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            '*' => false
                        ]
                    ]
                ],
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertSame('/', Home::alternative($user));
    }

    /**
     * @covers ::alternative
     */
    public function testAlternativeWithoutViewAccess()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'account'   => false,
                            'languages' => false,
                            'site'      => false,
                            'system'    => false,
                            'users'     => false,
                        ]
                    ]
                ],
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('There’s no available Panel page to redirect to');

        Home::alternative($user);
    }

    /**
     * @covers ::hasAccess
     */
    public function testHasAccess()
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertTrue(Home::hasAccess($user, 'site'));
        $this->assertTrue(Home::hasAccess($user, 'pages/test'));
        $this->assertTrue(Home::hasAccess($user, 'users/test@getkirby.com'));
        $this->assertTrue(Home::hasAccess($user, 'account'));

        // dialogs and dropdowns never get access
        $this->assertFalse(Home::hasAccess($user, 'dialogs/users/create'));
        $this->assertFalse(Home::hasAccess($user, 'dropdowns/users/test@getkirby.com'));

        // invalid routes return false
        $this->assertFalse(Home::hasAccess($user, 'does/not/exist/at/all'));

        // unauthenticated views return true
        $this->assertTrue(Home::hasAccess($user, 'browser'));
    }

    /**
     * @covers ::hasAccess
     */
    public function testHasAccessWithLimitedAccess()
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'site' => false
                        ]
                    ]
                ],
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $user = $this->app->impersonate('test@getkirby.com');

        $this->assertFalse(Home::hasAccess($user, 'site'));
        $this->assertFalse(Home::hasAccess($user, 'pages/test'));
        $this->assertTrue(Home::hasAccess($user, 'users/test@getkirby.com'));
        $this->assertTrue(Home::hasAccess($user, 'account'));
    }

    /**
     * @covers ::hasValidDomain
     */
    public function testHasValidDomain()
    {
        $uri = Uri::current();
        $this->assertTrue(Home::hasValidDomain($uri));

        $uri = new Uri('/');
        $this->assertTrue(Home::hasValidDomain($uri));

        $uri = new Uri('https://getkirby.com');
        $this->assertFalse(Home::hasValidDomain($uri));
    }

    /**
     * @covers ::isPanelUrl
     */
    public function testIsPanelUrl()
    {
        $this->assertTrue(Home::isPanelUrl('/panel'));
        $this->assertTrue(Home::isPanelUrl('/panel/pages/test'));
        $this->assertFalse(Home::isPanelUrl('test'));
    }

    /**
     * @covers ::panelPath
     */
    public function testPanelPath()
    {
        $this->assertSame('site', Home::panelPath('/panel/site'));
        $this->assertSame('pages/test', Home::panelPath('/panel/pages/test'));
        $this->assertSame('', Home::panelPath('/test/page'));
    }

    /**
     * @covers ::remembered
     */
    public function testRemembered()
    {
        $this->assertNull(Home::remembered());
    }

    /**
     * @covers ::remembered
     */
    public function testRememberedFromSession()
    {
        $this->app->session()->set('panel.path', 'users');
        $this->assertSame('/panel/users', Home::remembered());
    }

    /**
     * @covers ::url
     */
    public function testUrl()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');

        $this->assertSame('/panel/site', Home::url());
    }

    public function customHomeProvider()
    {
        return [
            // site url: /
            ['panel/pages/blog', '/panel/pages/blog'],
            ['panel/pages/blog?mean=attack', '/panel/pages/blog'],
            ['panel/pages/blog/mean:attack', '/panel/pages/blog'],
            ['{{ site.find("blog").panel.url }}', '/panel/pages/blog'],
            ['{{ site.url }}', '/'],

            // site url: https://getkirby.com
            ['panel/pages/blog', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
            ['panel/pages/blog?mean=attack', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
            ['panel/pages/blog/mean:attack', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
            ['{{ site.find("blog").panel.url }}', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
            ['{{ site.url }}', 'https://getkirby.com', 'https://getkirby.com'],
        ];
    }

    /**
     * @dataProvider customHomeProvider
     */
    public function testUrlWithCustomHome($url, $expected, $index = '/')
    {
        $this->app = $this->app->clone([
            'urls' => [
                'index' => $index
            ],
            'site' => [
                'children' => [
                    ['slug' => 'blog']
                ]
            ],
            'blueprints' => [
                'users/admin' => [
                    'name' => 'admin',
                    'home' => $url
                ]
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');

        $this->assertSame($expected, Home::url());
    }

    /**
     * @covers ::url
     */
    public function testUrlWithInvalidCustomHome()
    {
        $this->app = $this->app->clone([
            'urls' => [
                'index' => '/'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'blog']
                ]
            ],
            'blueprints' => [
                'users/admin' => [
                    'name' => 'admin',
                    'home' => 'https://getkirby.com'
                ]
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('External URLs are not allowed for Panel redirects');

        Home::url();
    }

    /**
     * @covers ::url
     */
    public function testUrlWithRememberedPath()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');
        $this->app->session()->set('panel.path', 'users');

        $this->assertSame('/panel/users', Home::url());
    }

    /**
     * @covers ::url
     */
    public function testUrlWithInvalidRememberedPath()
    {
        $this->app = $this->app->clone([
            'users' => [
                ['email' => 'test@getkirby.com', 'role' => 'admin']
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');
        $this->app->session()->set('panel.path', 'login');

        $this->assertSame('/panel/site', Home::url());
    }

    /**
     * @covers ::url
     */
    public function testUrlWithMissingSiteAccess()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'site' => false,
                        ]
                    ]
                ]
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $this->app->impersonate('editor@getkirby.com');
        $this->assertSame('/panel/users', Home::url());
    }

    /**
     * @covers ::url
     */
    public function testUrlWithAccountAccessOnly()
    {
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'permissions' => [
                        'access' => [
                            'site'  => false,
                            'users' => false,
                            'system' => false
                        ]
                    ]
                ]
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $this->app->impersonate('editor@getkirby.com');

        $this->assertSame('/panel/account', Home::url());
    }

    /**
     * @covers ::url
     */
    public function testUrlWithoutUser()
    {
        $this->assertSame('/panel/login', Home::url());
    }
}
