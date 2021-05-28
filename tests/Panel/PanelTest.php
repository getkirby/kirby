<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Panel
 */
class PanelTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PanelTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);

        // clear session file
        $this->app->session()->destroy();

        // clear fake json requests
        $_GET = [];
    }

    /**
     * @covers ::area
     */
    public function testArea(): void
    {
        // defaults
        $result = Panel::area('test', []);
        $expected = [
            'id' => 'test',
            'label' => 'test',
            'breadcrumb' => [],
            'breadcrumbLabel' => 'test',
            'title' => 'test',
            'menu' => false,
            'link' => 'test',
            'search' => null
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::areas
     */
    public function testAreas(): void
    {
        // unauthenticated / uninstalled
        $areas = Panel::areas($this->app);

        $this->assertArrayHasKey('installation', $areas);
        $this->assertCount(1, $areas);

        // fix installation issues by creating directories
        Dir::make($this->fixtures . '/content');
        Dir::make($this->fixtures . '/media');
        Dir::make($this->fixtures . '/site/accounts');
        Dir::make($this->fixtures . '/site/sessions');

        // create the first admin
        $this->app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        // let's pretend we are on a supported server
        $_SERVER['SERVER_SOFTWARE'] = 'php';

        // unauthenticated / installed
        $areas = Panel::areas($this->app);

        $this->assertArrayHasKey('login', $areas);
        $this->assertCount(1, $areas);

        // simulate a logged in user
        $this->app->impersonate('test@getkirby.com');

        // authenticated
        $areas = Panel::areas($this->app);

        $this->assertArrayHasKey('site', $areas);
        $this->assertArrayHasKey('settings', $areas);
        $this->assertArrayHasKey('users', $areas);
        $this->assertArrayHasKey('account', $areas);
        $this->assertCount(4, $areas);

        // authenticated with plugins
        $app = $this->app->clone([
            'areas' => [
                'todos' => function () {
                    return [];
                }
            ]
        ]);

        $app->impersonate('test@getkirby.com');

        $areas = Panel::areas($app);

        $this->assertArrayHasKey('todos', $areas);
        $this->assertCount(5, $areas);

        // clean up
        unset($_SERVER['SERVER_SOFTWARE']);
    }

    /**
     * @covers ::assets
     */
    public function testAssets(): void
    {
        // default asset setup
        $assets  = Panel::assets($this->app);
        $base    = '/media/panel/' . $this->app->versionHash();

        // css
        $this->assertSame($base . '/css/style.css', $assets['css']['index']);
        $this->assertSame('/media/plugins/index.css?0', $assets['css']['plugins']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame($base . '/js/vendor.js', $assets['js']['vendor']);
        $this->assertSame($base . '/js/plugins.js', $assets['js']['pluginloader']);
        $this->assertSame('/media/plugins/index.js?0', $assets['js']['plugins']);
        $this->assertSame($base . '/js/index.js', $assets['js']['index']);


        // dev mode
        $this->app = $this->app->clone([
            'request' => [
                'url' => 'http://sandbox.test'
            ],
            'options' => [
                'panel' => [
                    'dev' => true
                ]
            ]
        ]);

        $assets = Panel::assets($this->app);
        $base   = 'http://sandbox.test:3000';

        // css
        $this->assertSame(['plugins' => '/media/plugins/index.css?0'], $assets['css']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame([
            'pluginloader' => $base . '/js/plugins.js',
            'plugins' => '/media/plugins/index.js?0',
            'index' => $base . '/src/index.js',
            'vite' => $base . '/@vite/client'
        ], $assets['js']);


        // dev mode with custom url
        $this->app = $this->app->clone([
            'request' => [
                'url' => 'http://sandbox.test'
            ],
            'options' => [
                'panel' => [
                    'dev' => 'http://localhost:3000'
                ]
            ]
        ]);

        $assets = Panel::assets($this->app);
        $base   = 'http://localhost:3000';

        // css
        $this->assertSame(['plugins' => '/media/plugins/index.css?0'], $assets['css']);

        // icons
        $this->assertSame($base . '/apple-touch-icon.png', $assets['icons']['apple-touch-icon']['url']);
        $this->assertSame($base . '/favicon.svg', $assets['icons']['shortcut icon']['url']);
        $this->assertSame($base . '/favicon.png', $assets['icons']['alternate icon']['url']);

        // js
        $this->assertSame([
            'pluginloader' => $base . '/js/plugins.js',
            'plugins' => '/media/plugins/index.js?0',
            'index' => $base . '/src/index.js',
            'vite' => $base . '/@vite/client'
        ], $assets['js']);


        // custom panel css and js
        $this->app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => '/assets/panel.css',
                    'js'  => '/assets/panel.js',
                ]
            ]
        ]);

        // create dummy assets
        F::write($this->fixtures . '/assets/panel.css', 'test');
        F::write($this->fixtures . '/assets/panel.js', 'test');

        $assets = Panel::assets($this->app);

        $this->assertTrue(Str::contains($assets['css']['custom'], 'assets/panel.css'));
        $this->assertTrue(Str::contains($assets['js']['custom'], 'assets/panel.js'));
    }

    /**
     * @covers ::customCss
     */
    public function testCustomCss(): void
    {
        // invalid
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'nonexists.css'
                ]
            ]
        ]);

        $this->assertFalse(Panel::customCss($app));

        // valid
        F::write($this->fixtures . '/panel.css', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'panel.css'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Panel::customCss($app), '/panel.css'));
    }

    /**
     * @covers ::customJs
     */
    public function testCustomJs(): void
    {
        // invalid
        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'nonexists.js'
                ]
            ]
        ]);

        $this->assertFalse(Panel::customJs($app));

        // valid
        F::write($this->fixtures . '/panel.js', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'panel.js'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Panel::customJs($app), '/panel.js'));
    }

    /**
     * @covers ::data
     */
    public function testData(): void
    {
        // without custom data
        $data = Panel::data($this->app);

        $this->assertInstanceOf('Closure', $data['$language']);
        $this->assertInstanceOf('Closure', $data['$languages']);
        $this->assertInstanceOf('Closure', $data['$permissions']);
        $this->assertFalse($data['$license']);
        $this->assertFalse($data['$multilang']);
        $this->assertSame('/', $data['$url']);
        $this->assertInstanceOf('Closure', $data['$user']);
    }

    /**
     * @covers ::data
     */
    public function testDataWithCustomProps(): void
    {
        $data = Panel::data($this->app, [
            '$props' => $props = [
                'foo' => 'bar'
            ]
        ]);

        $this->assertSame($props, $data['$props']);
    }

    /**
     * @covers ::data
     */
    public function testDataWithLanguages(): void
    {
        $this->app = $this->app->clone([
            'languages' => [
                [ 'code' => 'en', 'name' => 'English', 'default' => true ],
                [ 'code' => 'de', 'name' => 'Deutsch'],
            ],
            'options' => [
                'languages' => true
            ]
        ]);

        // without custom data
        $data = Panel::data($this->app);

        // resolve lazy data
        $data = A::apply($data);

        $this->assertTrue($data['$multilang']);

        $expected = [
            [
                'code'    => 'en',
                'default' => true,
                'name'    => 'English'
            ],
            [
                'code'    => 'de',
                'default' => false,
                'name'    => 'Deutsch'
            ]
        ];

        $this->assertSame($expected, $data['$languages']);
        $this->assertSame($expected[0], $data['$language']);
    }

    /**
     * @covers ::data
     */
    public function testDataWithAuthenticatedUser(): void
    {
        // authenticate pseudo user
        $this->app->impersonate('kirby');

        // without custom data
        $data = Panel::data($this->app);

        // resolve lazy data
        $data = A::apply($data);

        // user
        $expected = [
            'email'    => 'kirby@getkirby.com',
            'id'       => 'kirby',
            'language' => 'en',
            'role'     => 'admin',
            'username' => 'kirby@getkirby.com'
        ];

        $this->assertSame($expected, $data['$user']);
        $this->assertSame($this->app->user()->role()->permissions()->toArray(), $data['$permissions']);
    }

    /**
     * @covers ::error
     */
    public function testError(): void
    {
        // without user
        $error = Panel::error($this->app, 'Test');

        $expected = [
            'component' => 'k-error-view',
            'props' => [
                'error' => 'Test',
                'layout' => 'outside'
            ],
            'title' => 'Error'
        ];

        $this->assertSame($expected, $error);

        // with user
        $this->app->impersonate('kirby');
        $error = Panel::error($this->app, 'Test');

        $this->assertSame('inside', $error['props']['layout']);

        // user without panel access
        $this->app->impersonate('nobody');
        $error = Panel::error($this->app, 'Test');

        $this->assertSame('outside', $error['props']['layout']);
    }

    /**
     * @covers ::fiber
     */
    public function testFiber(): void
    {
        // default
        $fiber = Panel::fiber($this->app, 'k-page-view');

        $expected = [
            '$language' => null,
            '$languages' => [],
            '$permissions' => null,
            '$license' => false,
            '$multilang' => false,
            '$url' => '/',
            '$user' => null
        ];

        $this->assertSame($expected, $fiber);
    }

    /**
     * @covers ::firewall
     * @covers ::hasAccess
     */
    public function testFirewallWithoutUser(): void
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to access the panel');

        // no user
        $this->assertFalse(Panel::hasAccess());
        Panel::firewall();
    }

    /**
     * @covers ::firewall
     * @covers ::hasAccess
     */
    public function testFirewallWithoutAcceptedUser(): void
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to access the panel');

        // user without panel access
        $this->app->impersonate('nobody');

        $this->assertFalse(Panel::hasAccess($this->app->user()));
        Panel::firewall($this->app->user());
    }

    /**
     * @covers ::firewall
     */
    public function testFirewallWithAcceptedUser(): void
    {
        // accepted user
        $this->app->impersonate('kirby');

        // general access
        $result = Panel::firewall($this->app->user());
        $this->assertTrue($result);

        $result = Panel::hasAccess($this->app->user());
        $this->assertTrue($result);

        // area access
        $result = Panel::firewall($this->app->user(), 'site');
        $this->assertTrue($result);

        $result = Panel::hasAccess($this->app->user(), 'site');
        $this->assertTrue($result);
    }

    /**
     * @covers ::firewall
     * @covers ::hasAccess
     */
    public function testFirewallWithoutAreaAccess(): void
    {
        $app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'editor'
                ]
            ],
            'blueprints' => [
                'users/editor' => [
                    'name' => 'editor',
                    'title' => 'Editor',
                    'permissions' => [
                        'access' => [
                            'settings' => false
                        ]
                    ]
                ]
            ]
        ]);

        // accepted user
        $app->impersonate('test@getkirby.com');

        // general access
        $result = Panel::firewall($app->user());
        $this->assertTrue($result);

        $result = Panel::hasAccess($app->user());
        $this->assertTrue($result);

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to access this part of the panel');

        // no area access
        $this->assertFalse(Panel::hasAccess($app->user(), 'settings'));
        Panel::firewall($app->user(), 'settings');
    }

    /**
     * @covers ::icons
     */
    public function testIcons(): void
    {
        $icons = Panel::icons($this->app);

        $this->assertNotNull($icons);
        $this->assertTrue(strpos($icons, '<svg', 0) !== false);
    }

    /**
     * @covers ::link
     */
    public function testLink(): void
    {
        // create links
        $link = Panel::link($this->app);
        $this->assertTrue($link);

        // try again to create links, should be return false
        $link = Panel::link($this->app);
        $this->assertFalse($link);
    }

    /**
     * @covers ::render
     */
    public function testRender(): void
    {
        // create panel dist files first to avoid redirect
        Panel::link($this->app);

        // get panel response
        $response = Panel::render($this->app, 'k-page-view', [
            'test' => 'Test'
        ]);

        $this->assertInstanceOf('\Kirby\Http\Response', $response);
        $this->assertSame(200, $response->code());
        $this->assertSame('text/html', $response->type());
        $this->assertSame('UTF-8', $response->charset());
        $this->assertNotNull($response->body());
    }

    /**
     * @covers ::render
     */
    public function testRenderJson(): void
    {
        // fake request data
        $_GET['_json'] = true;

        // get panel response
        $response = Panel::render($this->app, 'k-page-view', [
            'test' => 'Test'
        ]);

        $this->assertSame('application/json', $response->type());
        $this->assertSame('Accept', $response->header('Vary'));
        $this->assertSame('true', $response->header('X-Fiber'));
    }

    /**
     * @covers ::router
     */
    public function testRouterWithDisabledPanel(): void
    {
        $app = $this->app->clone([
            'options' => [
                'panel' => false
            ]
        ]);

        $result = Panel::router($app, '/');

        $this->assertFalse($result);
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguage(): void
    {
        $this->app = $this->app->clone([
            'options' => [
                'languages' => true,
            ],
            'languages' => [
                [
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch',
                ]
            ]
        ]);

        // set for the first time
        $language = Panel::setLanguage($this->app);

        $this->assertSame('en', $language);
        $this->assertSame('en', $this->app->session()->get('panel.language'));
        $this->assertSame('en', $this->app->language()->code());
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguageViaGet(): void
    {
        // switch via get request
        // needs to come first before the app is cloned
        $_GET['language'] = 'de';

        $this->app = $this->app->clone([
            'options' => [
                'languages' => true,
            ],
            'languages' => [
                [
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch',
                ]
            ]
        ]);

        // set for the first time
        $language = Panel::setLanguage($this->app);

        $this->assertSame('de', $language);
        $this->assertSame('de', $this->app->session()->get('panel.language'));
        $this->assertSame('de', $this->app->language()->code());
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguageInSingleLanugageSite(): void
    {
        $language = Panel::setLanguage($this->app);

        $this->assertNull($language);
        $this->assertNull($this->app->language());
    }

    /**
     * @covers ::setTranslation
     */
    public function testSetTranslation(): void
    {
        $translation = Panel::setTranslation($this->app);

        $this->assertSame('en', $translation);
        $this->assertSame('en', $this->app->translation()->code());
    }

    public function testSetTranslationViaUser(): void
    {
        $this->app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'language' => 'de',
                    'role' => 'admin'
                ]
            ]
        ]);

        $this->app->impersonate('test@getkirby.com');

        $translation = Panel::setTranslation($this->app);

        $this->assertSame('de', $translation);
        $this->assertSame('de', $this->app->translation()->code());
    }
}
