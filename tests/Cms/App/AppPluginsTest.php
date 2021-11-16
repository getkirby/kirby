<?php

namespace Kirby\Cms;

use Kirby\Cache\FileCache;
use Kirby\Cms\Auth\Challenge;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Mime;
use Kirby\Form\Field as FormField;
use Kirby\Image\Image;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\I18n;

require_once __DIR__ . '/../mocks.php';

class DummyAuthChallenge extends Challenge
{
    public static function isAvailable(User $user, string $mode): bool
    {
        return true;
    }

    public static function create(User $user, array $options): ?string
    {
        return 'test';
    }

    public static function verify(User $user, string $code): bool
    {
        return $code === 'test-verify';
    }
}

class DummyCache extends FileCache
{
}

class DummyFile extends File
{
}

class DummyPage extends Page
{
}

class DummyUser extends User
{
}

/**
 * @coversDefaultClass \Kirby\Cms\AppPlugins
 */
class AppPluginsTest extends TestCase
{
    public $fixtures;

    // used for testPluginLoader()
    public static $calledPluginsLoadedHook = false;

    public function setUp(): void
    {
        App::destroy();
    }

    public function testApi()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'api' => [
                'routes' => [
                    [
                        'pattern' => 'awesome',
                        'action'  => function () {
                            return 'nice';
                        }
                    ]
                ],
                'authentication' => function () {
                    return true;
                }
            ]
        ]);

        $kirby->impersonate('kirby');
        $this->assertEquals('nice', $kirby->call('api/awesome'));
    }

    public function testApiRoutePlugins()
    {
        App::plugin('test/a', [
            'api' => [
                'routes' => [
                    [
                        'pattern' => 'a',
                        'action'  => function () {
                            return 'a';
                        }
                    ]
                ]
            ]
        ]);

        App::plugin('test/b', [
            'api' => [
                'routes' => [
                    [
                        'pattern' => 'b',
                        'action'  => function () {
                            return 'b';
                        }
                    ]
                ]
            ]
        ]);

        App::plugin('test/c', [
            'api' => [
                'routes' => [
                    [
                        'pattern' => 'c',
                        'action'  => function () {
                            return 'c';
                        }
                    ]
                ]
            ]
        ]);

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'api' => [
                'authentication' => function () {
                    return true;
                }
            ],
        ]);

        $app->impersonate('kirby');

        $this->assertEquals('a', $app->api()->call('a'));
        $this->assertEquals('b', $app->api()->call('b'));
        $this->assertEquals('c', $app->api()->call('c'));
    }

    public function testApiRouteCallbackPlugins()
    {
        App::plugin('test/a', [
            'api' => [
                'routes' => function ($kirby) {
                    return [
                        [
                            'pattern' => 'a',
                            'action'  => function () use ($kirby) {
                                return $kirby->root('index');
                            }
                        ]
                    ];
                }
            ]
        ]);

        App::plugin('test/b', [
            'api' => [
                'routes' => function ($kirby) {
                    return [
                        [
                            'pattern' => 'b',
                            'action'  => function () {
                                return 'b';
                            }
                        ]
                    ];
                }
            ]
        ]);

        App::plugin('test/c', [
            'api' => [
                'routes' => [
                    [
                        'pattern' => 'c',
                        'action'  => function () {
                            return 'c';
                        }
                    ]
                ]
            ]
        ]);

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'api' => [
                'authentication' => function () {
                    return true;
                }
            ],
        ]);

        $app->impersonate('kirby');

        $this->assertEquals('/dev/null', $app->api()->call('a'));
        $this->assertEquals('b', $app->api()->call('b'));
        $this->assertEquals('c', $app->api()->call('c'));
    }

    public function testApiRouteCallbackPluginWithOptionAccess()
    {
        App::plugin('your/plugin', [
            'options' => [
                'test' => 'Test'
            ],
            'api' => [
                'routes' => function ($kirby) {
                    return [
                        [
                            'pattern' => 'test',
                            'action'  => function () use ($kirby) {
                                return $kirby->option('your.plugin.test');
                            }
                        ]
                    ];
                }
            ]
        ]);

        $app = new App([
            'options' => [
                'api.allowImpersonation' => true
            ],
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $app->impersonate('kirby');
        $this->assertEquals('Test', $app->api()->call('test'));
    }

    public function testAuthChallenge()
    {
        $kirby = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/AppPluginsTest/testAuthChallenge'
            ],
            'authChallenges' => [
                'dummy' => 'Kirby\Cms\DummyAuthChallenge'
            ],
            'options' => [
                'auth.challenges' => ['dummy']
            ],
            'users' => [
                [
                    'email' => 'homer@simpsons.com'
                ]
            ]
        ]);
        $auth    = $kirby->auth();
        $session = $kirby->session();

        $status = $auth->createChallenge('homer@simpsons.com');
        $this->assertSame([
            'challenge' => 'dummy',
            'email'     => 'homer@simpsons.com',
            'status'    => 'pending'
        ], $status->toArray());
        $this->assertSame('dummy', $status->challenge(false));
        $this->assertSame('homer@simpsons.com', $session->get('kirby.challenge.email'));
        $this->assertSame('dummy', $session->get('kirby.challenge.type'));
        $this->assertTrue(password_verify('test', $session->get('kirby.challenge.code')));
        $this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));

        $this->assertSame(
            $kirby->user('homer@simpsons.com'),
            $auth->verifyChallenge('test-verify')
        );

        $kirby->session()->destroy();
        Dir::remove($fixtures);
    }

    public function testBlueprint()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'pages/test' => $file = 'test.yml'
            ]
        ]);

        $this->assertEquals($file, $kirby->extension('blueprints', 'pages/test'));
    }

    public function testCacheType()
    {
        $kirby = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/AppPluginsTest/testCacheType'
            ],
            'cacheTypes' => [
                'file' => DummyCache::class
            ],
            'options' => [
                'cache' => [
                    'pages' => true
                ]
            ]
        ]);

        $this->assertInstanceOf(DummyCache::class, $kirby->cache('pages'));

        Dir::remove($fixtures);
    }

    public function testCollection()
    {
        $pages = new Pages([]);
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'collections' => [
                'test' => function () use ($pages) {
                    return $pages;
                }
            ],
        ]);

        $this->assertEquals($pages, $kirby->collection('test'));
    }

    public function testCollectionFilters()
    {

        // fetch all previous filters
        $prevFilters = Collection::$filters;

        Collection::$filters = [];

        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'collectionFilters' => [
                '**' => $filter = [
                    'validator' => function ($value, $test) {
                        return $value === 'foo';
                    }
                ]
            ]
        ]);

        $this->assertEquals(Collection::$filters['**'], $filter);

        // restore previous filters
        Collection::$filters = $prevFilters;
    }

    public function testController()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'controllers' => [
                'test' => function () {
                    return ['foo' => 'bar'];
                }
            ]
        ]);

        $this->assertEquals(['foo' => 'bar'], $kirby->controller('test'));
    }

    public function testFieldMethod()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'fieldMethods' => [
                'test' => function () {
                    return 'test';
                }
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->customField()->test());

        // reset methods
        Field::$methods = [];
    }

    public function testField()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'fields' => [
                'dummy' => __DIR__ . '/fixtures/fields/DummyField.php'
            ]
        ]);

        $page  = new Page(['slug' => 'test']);
        $field = new FormField('dummy', [
            'name'  => 'dummy',
            'peter' => 'shaw',
            'model' => $page
        ]);

        $this->assertInstanceOf(FormField::class, $field);
        $this->assertEquals('simpson', $field->homer());
        $this->assertEquals('shaw', $field->peter());
    }

    public function testKirbyTag()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'tags' => [
                'test' => [
                    'html' => function () {
                        return 'test';
                    }
                ],
                'FoO' => [
                    'html' => function () {
                        return 'test';
                    }
                ]
            ]
        ]);

        $this->assertEquals('test', $kirby->kirbytags('(test: foo)'));
        $this->assertEquals('test', $kirby->kirbytags('(TEST: foo)'));

        $this->assertEquals('test', $kirby->kirbytags('(foo: bar)'));
        $this->assertEquals('test', $kirby->kirbytags('(FOO: bar)'));
    }

    public function testPageMethod()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'pageMethods' => [
                'test' => function () {
                    return 'test';
                }
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->test());

        // reset methods
        Page::$methods = [];
    }

    public function testPagesMethod()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'pagesMethods' => [
                'test' => function () {
                    return 'test';
                }
            ]
        ]);

        $pages = new Pages([]);
        $this->assertEquals('test', $pages->test());

        // reset methods
        Pages::$methods = [];
    }

    public function testPageModel()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'pageModels' => [
                'dummy' => DummyPage::class
            ]
        ]);

        $page = Page::factory([
            'slug'  => 'test',
            'model' => 'dummy'
        ]);

        $this->assertInstanceOf(DummyPage::class, $page);
    }

    public function testPageModelFromFolder()
    {
        $kirby = new App([
            'roots' => [
                'index'  => '/dev/null',
                'models' => __DIR__ . '/fixtures/models'
            ]
        ]);

        $page = Page::factory([
            'slug' => 'test',
            'model' => 'test'
        ]);

        $this->assertInstanceOf('TestPage', $page);
    }

    public function testPermission()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'permissions' => [
                'test-category' => [
                    'test-action' => true,
                    'another'     => false
                ]
            ]
        ]);

        $permissions = new Permissions([]);
        $this->assertTrue($permissions->for('test-category', 'test-action'));
        $this->assertFalse($permissions->for('test-category', 'another'));

        // reset actions
        Permissions::$extendedActions = [];
    }

    public function testPermissionPlugin()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $plugin = new Plugin('kirby/manual', [
            'permissions' => [
                'test-action' => true,
                'another'     => false
            ]
        ]);

        $kirby->extend($plugin->extends(), $plugin);

        $permissions = new Permissions([]);
        $this->assertTrue($permissions->for('kirby.manual', 'test-action'));
        $this->assertFalse($permissions->for('kirby.manual', 'another'));

        // reset actions
        Permissions::$extendedActions = [];
    }

    public function testOption()
    {
        // simple
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'testOption' => 'testValue'
            ]
        ]);

        $this->assertEquals('testValue', $kirby->option('testOption'));
    }

    public function testExtensionsFromFolders()
    {
        Page::$models = [];

        $kirby = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/AppPluginsTest'
            ]
        ]);

        $expected = [
            'regular' => 'regularPage',
            'with.dot' => 'withdotPage',
            'with-dash' => 'withdashPage',
            'with_underscore' => 'withunderscorePage'
        ];

        $this->assertEquals($expected, Page::$models);
    }

    public function testExtensionsFromOptions()
    {
        $calledRoute = false;
        $calledHook  = false;

        $kirby = new App([
            'options' => [
                'routes' => [
                    [
                        'pattern' => 'test',
                        'action'  => function () use (&$calledRoute) {
                            $calledRoute = true;
                        }
                    ]
                ],
                'hooks' => [
                    'type.action:state' => function () use (&$calledHook) {
                        $calledHook = true;
                    }
                ]
            ]
        ]);

        $kirby->call('test');
        $kirby->trigger('type.action:state');
        $this->assertTrue($calledRoute);
        $this->assertTrue($calledHook);
    }

    public function testPluginOptions()
    {
        App::plugin('test/plugin', [
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        // simple
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'test.plugin' => [
                    'foo' => 'another-bar'
                ]
            ]
        ]);

        $this->assertSame('another-bar', $kirby->option('test.plugin.foo'));
        $this->assertSame(['foo' => 'another-bar'], $kirby->option('test.plugin'));
    }

    public function testPluginOptionsWithNonAssociativeArray()
    {
        // non-associative
        App::plugin('test/plugin', [
            'options' => [
                'foo' => ['one', 'two']
            ]
        ]);

        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'test.plugin' => [
                    'foo' => ['three']
                ]
            ]
        ]);

        $this->assertSame(['three'], $kirby->option('test.plugin.foo'));
    }

    public function testPluginOptionsWithAssociativeArray()
    {
        // associative
        App::plugin('test/plugin', [
            'options' => [
                'foo' => [
                    'a' => 'A',
                    'b' => 'B'
                ]
            ]
        ]);

        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'test.plugin' => [
                    'foo' => [
                        'a' => 'Custom A'
                    ]
                ]
            ]
        ]);

        $this->assertSame(['a' => 'Custom A', 'b' => 'B'], $kirby->option('test.plugin.foo'));
    }

    public function testRoutes()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'routes' => [
                [
                    'pattern' => 'test',
                    'action'  => function () {
                        return 'test';
                    }
                ]
            ]
        ]);

        $this->assertEquals('test', $kirby->call('test'));
    }

    public function testRoutesCallback()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'routes' => function () {
                return [
                    [
                        'pattern' => 'test',
                        'action'  => function () {
                            return 'test';
                        }
                    ]
                ];
            }
        ]);

        $this->assertEquals('test', $kirby->call('test'));
    }

    public function testSnippet()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'snippets' => [
                'header' => $file = 'header.php'
            ]
        ]);

        $this->assertEquals($file, $kirby->extension('snippets', 'header'));
    }

    public function testTemplate()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'templates' => [
                'project' => $file = 'project.php'
            ]
        ]);

        $this->assertEquals($file, $kirby->extension('templates', 'project'));
    }

    public function testTranslation()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'translations' => [
                'en' => [
                    'test' => 'English Test'
                ],
                'de' => [
                    'test' => 'Deutscher Test'
                ]
            ]
        ]);

        I18n::$locale = 'en';

        $this->assertEquals('English Test', I18n::translate('test'));

        I18n::$locale = 'de';

        $this->assertEquals('Deutscher Test', I18n::translate('test'));
    }

    public function testTranslationsInPlugin()
    {
        App::plugin('test/test', [
            'translations' => [
                'en' => [
                    'test' => 'English Test'
                ],
                'de' => [
                    'test' => 'Deutscher Test'
                ]
            ]
        ]);

        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        I18n::$locale = 'en';

        $this->assertEquals('English Test', I18n::translate('test'));

        I18n::$locale = 'de';

        $this->assertEquals('Deutscher Test', I18n::translate('test'));
    }

    public function testUserMethod()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'userMethods' => [
                'test' => function () {
                    return 'test';
                }
            ]
        ]);

        $user = new User([
            'email' => 'test@getkirby.com',
            'name'  => 'Test User'
        ]);
        $this->assertEquals('test', $user->test());

        // reset methods
        User::$methods = [];
    }

    public function testUserModel()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'userModels' => [
                'dummy' => DummyUser::class
            ]
        ]);

        $user = User::factory([
            'slug'  => 'test',
            'model' => 'dummy'
        ]);

        $this->assertInstanceOf(DummyUser::class, $user);
    }

    public function testUsersMethod()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'usersMethods' => [
                'test' => function () {
                    return 'test';
                }
            ]
        ]);

        $users = new Users([]);
        $this->assertEquals('test', $users->test());

        // reset methods
        Users::$methods = [];
    }

    public function testPluginLoader()
    {
        $phpUnit  = $this;
        $executed = 0;

        $kirby = new App([
            'roots' => [
                'index'   => $this->fixtures = __DIR__ . '/fixtures/AppPluginsTest',
                'plugins' => $this->fixtures . '/site/plugins-loader'
            ],
            'hooks' => [
                'system.loadPlugins:after' => function () use ($phpUnit, &$executed) {
                    if (count($this->plugins()) === 2) {
                        $phpUnit->assertEquals([
                            'kirby/manual1' => new Plugin('kirby/manual1', []),
                            'kirby/manual2' => new Plugin('kirby/manual2', [])
                        ], $this->plugins());
                    } else {
                        $phpUnit->assertEquals([
                            'kirby/test1' => new Plugin('kirby/test1', [
                                'hooks' => [
                                    'system.loadPlugins:after' => function () {
                                        // just a dummy closure to compare against
                                    }
                                ],
                                'root' => $phpUnit->fixtures . '/site/plugins-loader/test1'
                            ])
                        ], $this->plugins());
                    }

                    $executed++;
                }
            ]
        ]);

        // the hook defined inside the test1 plugin should also have been called
        $this->assertTrue(static::$calledPluginsLoadedHook);

        // try loading again (which should *not* trigger the hooks again)
        $kirby->plugins();

        // overwrite plugins with a custom array
        $expected = [
            'kirby/manual1' => new Plugin('kirby/manual1', []),
            'kirby/manual2' => new Plugin('kirby/manual2', [])
        ];
        $this->assertEquals($expected, $kirby->plugins($expected));

        // hook should have been called only once after the firs initialization
        $this->assertEquals(1, $executed);
    }

    public function testThirdPartyExtensions()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'tags' => [
                'test' => $testTag = function () {
                },
            ],
            'thirdParty' => [
                'blocks' => [
                    'test' => $testBlock = function () {
                    }
                ]
            ]
        ]);

        $this->assertSame($testTag, $kirby->extensions('tags')['test']);
        $this->assertSame($testBlock, $kirby->extensions('thirdParty')['blocks']['test']);
    }

    public function testNativeComponents()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'components' => [
                'url' => function ($kirby, $path) {
                    return 'https://rewritten.getkirby.com/' . $path;
                },
            ]
        ]);

        $this->assertEquals('https://rewritten.getkirby.com/test', $kirby->component('url')($kirby, 'test'));
        $this->assertEquals('https://getkirby.com/test', $kirby->nativeComponent('url')($kirby, 'test'));
    }

    /**
     * @covers ::extendAreas
     */
    public function testAreas()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'areas' => [
                'todos' => function () {
                    return [];
                }
            ]
        ]);

        $areas = $kirby->extensions('areas');

        $this->assertCount(1, $areas);
        $this->assertArrayHasKey('todos', $areas);
        $this->assertInstanceOf('Closure', $areas['todos'][0]);
    }

    /**
     * @covers ::extendFileTypes
     */
    public function testFileTypes()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'fileTypes' => [
                'm4p' => [
                    'mime' => 'video/m4p',
                    'type' => 'video',
                ],
                'heif' => [
                    'mime' => ['image/heic', 'image/heif'],
                    'type' => 'image',
                    'resizable' => true,
                    'viewable' => true,
                ],
                'test' => [
                    'extension' => 'kql',
                    'type' => 'code'
                ],
                'midi' => [
                    'mime' => 'audio/x-midi'
                ],
                'ttf' => [
                    'type' => 'font'
                ]
            ]
        ]);

        $fileTypes = $kirby->extensions('fileTypes');
        $this->assertSame($fileTypes['type'], F::$types);
        $this->assertSame($fileTypes['mime'], Mime::$types);
        $this->assertSame($fileTypes['resizable'], Image::$resizableTypes);
        $this->assertSame($fileTypes['viewable'], Image::$viewableTypes);

        $this->assertContains('m4p', F::$types['video']);
        $this->assertArrayHasKey('m4p', Mime::$types);
        $this->assertSame('video/m4p', Mime::$types['m4p']);
        $this->assertNotContains('m4p', Image::$resizableTypes);
        $this->assertNotContains('m4p', Image::$viewableTypes);

        $this->assertContains('heif', F::$types['image']);
        $this->assertArrayHasKey('heif', Mime::$types);
        $this->assertSame(['image/heic', 'image/heif'], Mime::$types['heif']);
        $this->assertContains('heif', Image::$resizableTypes);
        $this->assertContains('heif', Image::$viewableTypes);

        $this->assertContains('kql', F::$types['code']);
        $this->assertNotContains('kql', Image::$resizableTypes);
        $this->assertNotContains('kql', Image::$viewableTypes);

        $this->assertArrayHasKey('midi', Mime::$types);
        $this->assertSame(['audio/midi', 'audio/x-midi'], Mime::$types['midi']);
        $this->assertNotContains('midi', Image::$resizableTypes);
        $this->assertNotContains('midi', Image::$viewableTypes);

        $this->assertArrayHasKey('font', F::$types);
        $this->assertContains('ttf', F::$types['font']);
        $this->assertNotContains('ttf', Image::$resizableTypes);
        $this->assertNotContains('ttf', Image::$viewableTypes);
    }
}
