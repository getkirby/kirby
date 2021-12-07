<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\Toolkit\A;
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
        Blueprint::$loaded = [];

        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
        // fix installation issues by creating directories
        Dir::make($this->tmp . '/content');
        Dir::make($this->tmp . '/media');
        Dir::make($this->tmp . '/site/accounts');
        Dir::make($this->tmp . '/site/sessions');

        // let's pretend we are on a supported server
        $_SERVER['SERVER_SOFTWARE'] = 'php';
    }

    public function tearDown(): void
    {
        // clear session file first
        $this->app->session()->destroy();

        Dir::remove($this->tmp);

        // clear fake json requests
        $_GET = [];

        // clean up $_SERVER
        unset($_SERVER['SERVER_SOFTWARE']);
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

        // create the first admin
        $this->app = $this->app->clone([
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        // unauthenticated / installed
        $areas = Panel::areas($this->app);

        $this->assertArrayHasKey('login', $areas);
        $this->assertCount(1, $areas);

        // simulate a logged in user
        $this->app->impersonate('test@getkirby.com');

        // authenticated
        $areas = Panel::areas($this->app);

        $this->assertArrayHasKey('site', $areas);
        $this->assertArrayHasKey('system', $areas);
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
                            'system' => false
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
        $this->assertFalse(Panel::hasAccess($app->user(), 'system'));
        Panel::firewall($app->user(), 'system');
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
     * @covers ::multilang
     */
    public function testMultilang()
    {
        $this->app = $this->app->clone([
            'options' => [
                'languages' => true
            ]
        ]);

        $this->assertTrue(Panel::multilang());
    }

    /**
     * @covers ::multilang
     */
    public function testMultilangWithImplicitLanguageInstallation()
    {
        $this->app = $this->app->clone([
            'languages' => [
                [
                    'code' => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $this->assertTrue(Panel::multilang());
    }

    /**
     * @covers ::multilang
     */
    public function testMultilangDisabled()
    {
        $this->assertFalse(Panel::multilang());
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
     * @covers ::routes
     */
    public function testRoutes()
    {
        $routes = Panel::routes([]);

        $this->assertSame('browser', $routes[0]['pattern']);
        $this->assertSame(['/', 'installation', 'login'], $routes[1]['pattern']);
        $this->assertSame('(:all)', $routes[2]['pattern']);
        $this->assertSame('The view could not be found', $routes[2]['action']());
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
     * @covers ::routesForDialogs
     */
    public function testRoutesForDialogsWithoutHandlers(): void
    {
        $area = [
            'dialogs' => [
                'test' => []
            ]
        ];

        $routes = Panel::routesForDialogs('test', $area);

        $this->assertSame('The load handler for your dialog is missing', $routes[0]['action']());
        $this->assertSame('Your dialog does not define a submit handler', $routes[1]['action']());
    }

    /**
     * @covers ::routesForDropdowns
     */
    public function testRoutesForDropdowns(): void
    {
        $area = [
            'dropdowns' => [
                'test' => [
                    'pattern' => 'test',
                    'action'  => $action = function () {
                        return [
                            [
                                'text' => 'Test',
                                'link' => '/test'
                            ]
                        ];
                    }
                ]
            ]
        ];

        $routes = Panel::routesForDropdowns('test', $area);

        $expected = [
            [
                'pattern' => 'dropdowns/test',
                'type'    => 'dropdown',
                'area'    => 'test',
                'method'  => 'GET|POST',
                'action'  => $action,
            ]
        ];

        $this->assertSame($expected, $routes);
    }

    /**
     * @covers ::routesForDropdowns
     */
    public function testRoutesForDropdownsWithOptions(): void
    {
        $area = [
            'dropdowns' => [
                'test' => [
                    'pattern' => 'test',
                    'options' => $action = function () {
                        return [
                            [
                                'text' => 'Test',
                                'link' => '/test'
                            ]
                        ];
                    }
                ]
            ]
        ];

        $routes = Panel::routesForDropdowns('test', $area);

        $expected = [
            [
                'pattern' => 'dropdowns/test',
                'type'    => 'dropdown',
                'area'    => 'test',
                'method'  => 'GET|POST',
                'action'  => $action,
            ]
        ];

        $this->assertSame($expected, $routes);
    }

    /**
     * @covers ::routesForDropdowns
     */
    public function testRoutesForDropdownsWithShortcut(): void
    {
        $area = [
            'dropdowns' => [
                'test' => $action = function () {
                    return [
                        [
                            'text' => 'Test',
                            'link' => '/test'
                        ]
                    ];
                }
            ]
        ];

        $routes = Panel::routesForDropdowns('test', $area);

        $expected = [
            [
                'pattern' => 'dropdowns/test',
                'type'    => 'dropdown',
                'area'    => 'test',
                'method'  => 'GET|POST',
                'action'  => $action,
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
            'views' => [
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
        $language = Panel::setLanguage();

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
        $language = Panel::setLanguage();

        $this->assertSame('de', $language);
        $this->assertSame('de', $this->app->language()->code());

        // language is now stored in the session after request query
        $this->assertSame('de', $this->app->session()->get('panel.language'));
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguageWithCustomDefault(): void
    {
        $this->app = $this->app->clone([
            'languages' => [
                [
                    'code' => 'de',
                    'name' => 'Deutsch',
                    'default' => true
                ],
                [
                    'code' => 'en',
                    'name' => 'English',
                ],
            ],
            'options' => [
                'languages' => true,
            ]
        ]);

        // set for the first time
        $language = Panel::setLanguage();

        $this->assertSame('de', $language);
        $this->assertSame('de', $this->app->language()->code());
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
        $language = Panel::setLanguage();

        $this->assertSame('de', $language);
        $this->assertSame('de', $this->app->session()->get('panel.language'));
        $this->assertSame('de', $this->app->language()->code());
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguageInSingleLanguageSite(): void
    {
        $language = Panel::setLanguage();

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
