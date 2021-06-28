<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Panel
 */
class PanelTest extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        // clear session file first
        $this->app->session()->destroy();

        Dir::remove($this->tmp);

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
        $areas = Panel::areas();

        $this->assertArrayHasKey('installation', $areas);
        $this->assertCount(1, $areas);

        // fix installation issues by creating directories
        Dir::make($this->tmp . '/content');
        Dir::make($this->tmp . '/media');
        Dir::make($this->tmp . '/site/accounts');
        Dir::make($this->tmp . '/site/sessions');

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
        $assets  = Panel::assets();
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

        // add vite file
        F::write($viteFile = $this->app->roots()->panel() . '/.vite-running', '');

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
        F::write($this->tmp . '/assets/panel.css', 'test');
        F::write($this->tmp . '/assets/panel.js', 'test');

        $assets = Panel::assets($this->app);

        $this->assertTrue(Str::contains($assets['css']['custom'], 'assets/panel.css'));
        $this->assertTrue(Str::contains($assets['js']['custom'], 'assets/panel.js'));

        // clean up vite file
        F::remove($viteFile);
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

        $this->assertNull(Panel::customCss());

        // valid
        F::write($this->tmp . '/panel.css', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'css' => 'panel.css'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Panel::customCss(), '/panel.css'));
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

        $this->assertNull(Panel::customJs());

        // valid
        F::write($this->tmp . '/panel.js', '');

        $app = $this->app->clone([
            'options' => [
                'panel' => [
                    'js' => 'panel.js'
                ]
            ]
        ]);

        $this->assertTrue(Str::contains(Panel::customJs(), '/panel.js'));
    }

    /**
     * @covers ::data
     */
    public function testData(): void
    {
        // without custom data
        $data = Panel::data();

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
    public function testDataWithIncludedGlobal(): void
    {
        // not included
        $data = Panel::data();
        $this->assertArrayNotHasKey('$translation', $data);

        // via query
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_globals' => '$translation'
                ]
            ]
        ]);

        $data = Panel::data();
        $this->assertArrayHasKey('$translation', $data);

        // via header
        $this->app = $this->app->clone([
            'request' => [
                'headers' => [
                    'X-Fiber-Globals' => '$translation'
                ]
            ]
        ]);

        $data = Panel::data();
        $this->assertArrayHasKey('$translation', $data);
    }

    /**
     * @covers ::data
     */
    public function testDataWithCustomProps(): void
    {
        $data = Panel::data([
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
        $data = Panel::data();

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
        $data = Panel::data();

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
     * @covers ::errorDialog
     */
    public function testErrorDialog(): void
    {
        // default
        $error = Panel::errorDialog('Test');

        $this->assertSame(404, $error['code']);
        $this->assertSame('Test', $error['error']);

        // custom code
        $error = Panel::errorDialog('Test', 500);

        $this->assertSame(500, $error['code']);
        $this->assertSame('Test', $error['error']);
    }

    /**
     * @covers ::errorView
     */
    public function testErrorView(): void
    {
        // without user
        $error = Panel::errorView('Test');

        $expected = [
            'code' => 404,
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
        $error = Panel::errorView('Test');

        $this->assertSame('inside', $error['props']['layout']);

        // user without panel access
        $this->app->impersonate('nobody');
        $error = Panel::errorView('Test');

        $this->assertSame('outside', $error['props']['layout']);
    }

    /**
     * @covers ::errorView
     */
    public function testErrorViewWithCustomCode(): void
    {
        $error = Panel::errorView('Test', 403);
        $this->assertSame(403, $error['code']);
    }

    /**
     * @covers ::fiber
     */
    public function testFiber(): void
    {
        // default
        $fiber = Panel::fiber();

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
    public function testFirewallAreaAccess(): void
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

        // no defined area permissions means access
        $this->assertTrue(Panel::hasAccess($app->user(), 'foo'));
        Panel::firewall($app->user(), 'foo');

        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionMessage('You are not allowed to access this part of the panel');

        // no area access
        $this->assertFalse(Panel::hasAccess($app->user(), 'settings'));
        Panel::firewall($app->user(), 'settings');
    }


    /**
     * @covers ::globals
     */
    public function testGlobals(): void
    {
        // defaults
        $globals = Panel::globals();

        $this->assertInstanceOf('Closure', $globals['$config']);
        $this->assertInstanceOf('Closure', $globals['$system']);
        $this->assertInstanceOf('Closure', $globals['$system']);
        $this->assertInstanceOf('Closure', $globals['$translation']);
        $this->assertInstanceOf('Closure', $globals['$urls']);

        // defaults after apply
        $globals     = A::apply($globals);
        $config      = $globals['$config'];
        $system      = $globals['$system'];
        $translation = $globals['$translation'];
        $urls        = $globals['$urls'];

        // $config
        $this->assertFalse($config['debug']);
        $this->assertTrue($config['kirbytext']);
        $this->assertSame(['limit' => 10, 'type'  => 'pages'], $config['search']);
        $this->assertSame('en', $config['translation']);

        // $system
        $this->assertSame(Str::$ascii, $system['ascii']);
        $this->assertSame(csrf(), $system['csrf']);
        $this->assertFalse($system['isLocal']);
        $this->assertArrayHasKey('de', $system['locales']);
        $this->assertArrayHasKey('en', $system['locales']);
        $this->assertSame('en_US', $system['locales']['en']);
        $this->assertSame('de_DE', $system['locales']['de']);

        // $translation
        $this->assertSame('en', $translation['code']);
        $this->assertSame($this->app->translation('en')->dataWithFallback(), $translation['data']);
        $this->assertSame('ltr', $translation['direction']);
        $this->assertSame('English', $translation['name']);

        // $urls
        $this->assertSame('/api', $urls['api']);
        $this->assertSame('/', $urls['site']);
    }

    /**
     * @covers ::globals
     */
    public function testGlobalsWithUser(): void
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
        $globals = Panel::globals();
        $globals = A::apply($globals);
        $this->assertSame('de', $globals['$translation']['code']);
    }

    /**
     * @covers ::go
     */
    public function testGo()
    {
        $thrown = false;
        try {
            Panel::go('test');
        } catch (Redirect $r) {
            $thrown = true;
            $this->assertSame('/panel/test', $r->getMessage());
            $this->assertSame(302, $r->getCode());
        }
        $this->assertTrue($thrown);
    }

    /**
     * @covers ::go
     */
    public function testGoWithCustomCode()
    {
        try {
            Panel::go('test', 301);
        } catch (Redirect $r) {
            $this->assertSame(301, $r->getCode());
        }
    }

    /**
     * @covers ::go
     */
    public function testGoWithCustomSlug()
    {
        $this->app = $this->app->clone([
            'options' => [
                'panel' => [
                    'slug' => 'foo'
                ]
            ]
        ]);

        try {
            Panel::go('test');
        } catch (Redirect $r) {
            $this->assertSame('/foo/test', $r->getMessage());
            $this->assertSame(302, $r->getCode());
        }
    }

    /**
     * @covers ::icons
     */
    public function testIcons(): void
    {
        $icons = Panel::icons();

        $this->assertNotNull($icons);
        $this->assertTrue(strpos($icons, '<svg', 0) !== false);
    }

    /**
     * @covers ::isFiberRequest
     */
    public function testIsFiberRequest(): void
    {
        // standard request
        $result = Panel::isFiberRequest($this->app->request());
        $this->assertFalse($result);

        // fiber request via get
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true
                ]
            ]
        ]);

        $result = Panel::isFiberRequest($this->app->request());
        $this->assertTrue($result);

        // fiber request via header
        $this->app = $this->app->clone([
            'request' => [
                'headers' => [
                    'X-Fiber' => true
                ]
            ]
        ]);

        $result = Panel::isFiberRequest($this->app->request());
        $this->assertTrue($result);

        // other request than GET
        $this->app = $this->app->clone([
            'request' => [
                'method' => 'POST'
            ]
        ]);

        $result = Panel::isFiberRequest($this->app->request());
        $this->assertFalse($result);
    }

    /**
     * @covers ::json
     */
    public function testJson(): void
    {
        $response = Panel::json($data = ['foo' => 'bar']);

        $this->assertSame('application/json', $response->type());
        $this->assertSame('true', $response->header('X-Fiber'));
        $this->assertSame($data, json_decode($response->body(), true));
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
     * @covers ::partial
     */
    public function testPartial()
    {
        $data = [
            'a' => 'A',
            'b' => 'B'
        ];

        // default (no partial request)
        $result = Panel::partial($data);

        $this->assertSame($data, $result);
    }

    /**
     * @covers ::partial
     */
    public function testPartialWithOnly(): void
    {
        // via get
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_only' => 'a',
                ]
            ]
        ]);

        $data = [
            'a' => 'A',
            'b' => 'B'
        ];

        $result = Panel::partial($data);

        $this->assertSame(['a' => 'A'], $result);

        // via headers
        $this->app = $this->app->clone([
            'request' => [
                'headers' => [
                    'X-Fiber-Only' => 'a',
                ]
            ]
        ]);

        $data = [
            'a' => 'A',
            'b' => 'B'
        ];

        $result = Panel::partial($data);

        $this->assertSame(['a' => 'A'], $result);
    }

    /**
     * @covers ::partial
     */
    public function testPartialWithGlobal(): void
    {
        // simulate a simple partial request
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_only' => 'a,$urls',
                ]
            ]
        ]);

        $data = [
            'a' => 'A',
            'b' => 'B'
        ];

        $result = Panel::partial($data);
        $result = A::apply($result);

        $expected = [
            'a' => 'A',
            '$urls' => [
                'api' => '/api',
                'site' => '/'
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::partial
     */
    public function testPartialWithNestedData(): void
    {
        // simulate a simple partial request
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_only' => 'b.c',
                ]
            ]
        ]);

        $data = [
            'a' => 'A',
            'b' => [
                'c' => 'C'
            ]
        ];

        $result = Panel::partial($data);

        $expected = [
            'b' => [
                'c' => 'C'
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::partial
     */
    public function testPartialWithNestedGlobal(): void
    {
        // simulate a simple partial request
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_only' => 'a,$urls.site',
                ]
            ]
        ]);

        $data = [
            'a' => 'A',
            'b' => 'B'
        ];

        $result = Panel::partial($data);

        $expected = [
            'a' => 'A',
            '$urls' => [
                'site' => '/'
            ]
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::response
     */
    public function testResponse()
    {
        $response = new Response('Test');

        // response objects should not be modified
        $this->assertSame($response, Panel::response($response));
    }

    /**
     * @covers ::response
     */
    public function testResponseFromNullOrFalse()
    {
        // fake json request for easier assertions
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ]
        ]);

        // null is interpreted as 404
        $response = Panel::response(null);
        $json     = json_decode($response->body(), true);

        $this->assertSame(404, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('The data could not be found', $json['$view']['props']['error']);

        // false is interpreted as 404
        $response = Panel::response(false);
        $json     = json_decode($response->body(), true);

        $this->assertSame(404, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('The data could not be found', $json['$view']['props']['error']);
    }

    /**
     * @covers ::response
     */
    public function testResponseFromString()
    {
        // fake json request for easier assertions
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ]
        ]);

        // strings are interpreted as errors
        $response = Panel::response('Test');
        $json     = json_decode($response->body(), true);

        $this->assertSame(500, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('Test', $json['$view']['props']['error']);
    }

    /**
     * @covers ::responseForDialog
     */
    public function testResponseForDialog(): void
    {
        $response = Panel::responseForDialog([
            'test' => 'Test'
        ]);

        $expected = [
            '$dialog' => [
                'test'     => 'Test',
                'code'     => 200,
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame('application/json', $response->type());
        $this->assertSame('true', $response->header('X-Fiber'));
        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::responseForDialog
     */
    public function testResponseForDialogFromTrue(): void
    {
        $response = Panel::responseForDialog(true);
        $expected = [
            '$dialog' => [
                'code'     => 200,
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::responseForDialog
     */
    public function testResponseForDialogFromInvalidData(): void
    {
        $response = Panel::responseForDialog(1234);
        $expected = [
            '$dialog' => [
                'code'     => 500,
                'error'    => 'Invalid dialog response',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::responseForDialog
     */
    public function testResponseForDialogFromException(): void
    {
        $exception = new \Exception('Test');
        $response  = Panel::responseForDialog($exception);
        $expected  = [
            '$dialog' => [
                'code'     => 500,
                'error'    => 'Test',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::responseForDialog
     */
    public function testResponseForDialogFromKirbyException(): void
    {
        $exception = new \Kirby\Exception\NotFoundException('Test');
        $response  = Panel::responseForDialog($exception);
        $expected  = [
            '$dialog' => [
                'code'     => 404,
                'error'    => 'Test',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::responseForView
     */
    public function testResponseForViewAsHTML(): void
    {
        // create panel dist files first to avoid redirect
        Panel::link($this->app);

        // get panel response
        $response = Panel::responseForView([
            'test' => 'Test'
        ]);

        $this->assertInstanceOf('\Kirby\Http\Response', $response);
        $this->assertSame(200, $response->code());
        $this->assertSame('text/html', $response->type());
        $this->assertSame('UTF-8', $response->charset());
        $this->assertNotNull($response->body());
    }

    /**
     * @covers ::responseForView
     */
    public function testResponseForViewAsJson(): void
    {
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true
                ]
            ]
        ]);

        // get panel response
        $response = Panel::responseForView([
            'test' => 'Test'
        ]);

        $this->assertSame('application/json', $response->type());
        $this->assertSame('true', $response->header('X-Fiber'));
    }

    /**
     * @covers ::responseForView
     */
    public function testResponseForViewFromKirbyException()
    {
        // fake json request for easier assertions
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ]
        ]);

        $exception = new \Kirby\Exception\NotFoundException('Test');
        $response  = Panel::responseForView($exception);
        $json      = json_decode($response->body(), true);

        $this->assertSame(404, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('Test', $json['$view']['props']['error']);
    }

    /**
     * @covers ::responseForView
     */
    public function testResponseForViewFromException()
    {
        // fake json request for easier assertions
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ]
        ]);

        $exception = new \Exception('Test');
        $response  = Panel::responseForView($exception);
        $json      = json_decode($response->body(), true);

        $this->assertSame(500, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('Test', $json['$view']['props']['error']);
    }

    /**
     * @covers ::responseForView
     */
    public function testResponseForViewFromUnsupportedResult()
    {
        // fake json request for easier assertions
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    '_json' => true,
                ]
            ]
        ]);

        $response = Panel::responseForView(1234);
        $json     = json_decode($response->body(), true);

        $this->assertSame(500, $response->code());
        $this->assertSame('k-error-view', $json['$view']['component']);
        $this->assertSame('Invalid Panel response', $json['$view']['props']['error']);
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

        $result = Panel::router('/');

        $this->assertNull($result);
    }

    /**
     * @covers ::routesForDialogs
     */
    public function testRoutesForDialogs(): void
    {
        $area = [
            'dialogs' => [
                'test' => [
                    'load'   => $load   = function () {
                    },
                    'submit' => $submit = function () {
                    },
                ]
            ]
        ];

        $routes = Panel::routesForDialogs('test', $area);

        $expected = [
            [
                'pattern' => 'dialogs/test',
                'type'    => 'dialog',
                'area'    => 'test',
                'action'  => $load,
            ],
            [
                'pattern' => 'dialogs/test',
                'type'    => 'dialog',
                'area'    => 'test',
                'method'  => 'POST',
                'action'  => $submit,
            ]
        ];

        $this->assertSame($expected, $routes);
    }

    /**
     * @covers ::routesForViews
     */
    public function testRoutesForViews(): void
    {
        $area = [
            'routes' => [
                [
                    'pattern' => 'test',
                    'action'  => $callback = function () {
                    }
                ]
            ]
        ];

        $routes = Panel::routesForViews('test', $area);

        $expected = [
            [
                'pattern' => 'test',
                'action'  => $callback,
                'area'    => 'test',
                'type'    => 'view'
            ]
        ];

        $this->assertSame($expected, $routes);
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguageWithoutRequest(): void
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
        $this->assertSame('en', $this->app->language()->code());

        // language is not stored in the session yet
        $this->assertNull($this->app->session()->get('panel.language'));
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguage(): void
    {
        $this->app = $this->app->clone([
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
            ],
            'options' => [
                'languages' => true,
            ],
            'request' => [
                'query' => [
                    'language' => 'de'
                ]
            ]
        ]);

        // set for the first time
        $language = Panel::setLanguage($this->app);

        $this->assertSame('de', $language);
        $this->assertSame('de', $this->app->language()->code());

        // language is now stored in the session after request query
        $this->assertSame('de', $this->app->session()->get('panel.language'));
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

    /**
     * @covers ::view
     */
    public function testView(): void
    {
        // defaults
        $result = Panel::view();
        $expected = [
            'breadcrumb' => [],
            'code' => 200,
            'path' => '',
            'timestamp' => $result['timestamp'],
            'props' => [],
            'search' => 'pages'
        ];

        $this->assertSame([], $result['breadcrumb']);
        $this->assertSame(200, $result['code']);
        $this->assertSame('', $result['path']);
        $this->assertArrayHaskey('timestamp', $result);
        $this->assertSame([], $result['props']);
        $this->assertSame('pages', $result['search']);

        // with $view
        $result = Panel::view(['search' => 'files']);
        $expected['search'] = 'files';
        $this->assertEquals($expected, $result);

        // with $area
        $result = Panel::view(['search' => 'users'], ['title' => 'Users']);
        $expected['search'] = 'users';
        $expected['title']  = 'Users';
        $this->assertEquals($expected, $result);

        // make sure routes are unset
        $result = Panel::view(['search' => 'users'], ['title' => 'Users', 'routes' => ['foo' => 'bar']]);
        $this->assertEquals($expected, $result);
    }
}
