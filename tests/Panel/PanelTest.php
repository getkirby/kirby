<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
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
    public function testArea()
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
    public function testAreas()
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

        $this->assertTrue(strpos(Panel::customCss($app), '//panel.css', 0) !== false);
    }

    /**
     * @covers ::error
     */
    public function testError()
    {
        // without user
        $error = Panel::error($this->app, 'Test');

        $expected = [
            'component' => 'k-error-view',
            'props' => [
                'error' => 'Test',
                'layout' => 'outside'
            ],
            'view' => [
                'title' => 'Error'
            ]
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
     * @covers ::firewall
     * @covers ::hasAccess
     */
    public function testFirewallWithoutUser()
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
    public function testFirewallWithoutAcceptedUser()
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
    public function testFirewallWithAcceptedUser()
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
    public function testFirewallWithoutAreaAccess()
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
    public function testRenderJson()
    {
        // fake request data
        $_GET['json'] = true;

        // get panel response
        $response = Panel::render($this->app, 'k-page-view', [
            'test' => 'Test'
        ]);

        $this->assertSame('application/json', $response->type());
        $this->assertSame('Accept', $response->header('Vary'));
        $this->assertSame('true', $response->header('X-Inertia'));
    }

    /**
     * @covers ::router
     */
    public function testRouterWithDisabledPanel()
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
    public function testSetLanguage()
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
    public function testSetLanguageViaGet()
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
    public function testSetLanguageInSingleLanugageSite()
    {
        $language = Panel::setLanguage($this->app);

        $this->assertNull($language);
        $this->assertNull($this->app->language());
    }

    /**
     * @covers ::setTranslation
     */
    public function testSetTranslation()
    {
        $translation = Panel::setTranslation($this->app);

        $this->assertSame('en', $translation);
        $this->assertSame('en', $this->app->translation()->code());
    }

    public function testSetTranslationViaUser()
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
