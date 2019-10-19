<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Http\Route;

/**
 * @coversDefaultClass \Kirby\Cms\App
 */
class AppTest extends TestCase
{
    protected $fixtures;

    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/AppTest';
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    /**
     * @covers ::apply
     */
    public function testApply()
    {
        $app = new App([
            'options' => [
                'hooks' => [
                    'noModify' => [
                        function ($value) {
                            // don't return anything
                        },
                        function ($value) {
                            // explicitly return null (should be the same internally)
                            return null;
                        }
                    ],
                    'singleParam' => [
                        function ($value) {
                            if (func_num_args() !== 1) {
                                throw new \Exception();
                            }

                            return $value * 2;
                        },
                        function ($value) {
                            // don't return anything
                        },
                        function ($value) {
                            return $value + 1;
                        },
                    ],
                    'multiParams' => [
                        function ($arg1, $arg2, $value) {
                            if (func_num_args() !== 3 || $arg1 !== 'arg1' || $arg2 !== 'arg2') {
                                throw new \Exception();
                            }

                            return $value * 2;
                        },
                        function ($arg1, $arg2, $value) {
                            if (func_num_args() !== 3 || $arg1 !== 'arg1' || $arg2 !== 'arg2') {
                                throw new \Exception();
                            }

                            // don't return anything
                        },
                        function ($arg1, $arg2, $value) {
                            if (func_num_args() !== 3 || $arg1 !== 'arg1' || $arg2 !== 'arg2') {
                                throw new \Exception();
                            }

                            return $value + 1;
                        },
                    ]
                ]
            ]
        ]);

        $this->assertEquals(10, $app->apply('noModify', 10));

        $this->assertEquals(5, $app->apply('singleParam', 2));
        $this->assertEquals(21, $app->apply('singleParam', 10));

        $this->assertEquals(5, $app->apply('multiParams', 'arg1', 'arg2', 2));
        $this->assertEquals(21, $app->apply('multiParams', 'arg1', 'arg2', 10));

        $this->assertEquals(2, $app->apply('does-not-exist', 2));
    }

    public function testDebugInfo()
    {
        $app = new App();
        $debuginfo = $app->__debugInfo();

        $this->assertArrayHasKey('languages', $debuginfo);
        $this->assertArrayHasKey('options', $debuginfo);
        $this->assertArrayHasKey('request', $debuginfo);
        $this->assertArrayHasKey('roots', $debuginfo);
        $this->assertArrayHasKey('site', $debuginfo);
        $this->assertArrayHasKey('urls', $debuginfo);
        $this->assertArrayHasKey('version', $debuginfo);
    }

    public function testDefaultRoles()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/does-not-exist'
            ]
        ]);

        $this->assertInstanceOf(Roles::class, $app->roles());
    }

    public function testEmail()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $email = $app->email(
            [
                'from'    => 'test@getkirby.com',
                'to'      => 'test@getkirby.com',
                'body'    => 'test',
                'subject' => 'Test'
            ],
            [
                'debug'   => true
            ]
        );

        $this->assertInstanceOf('Kirby\Email\PHPMailer', $email);
    }

    public function testOption()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertEquals('bar', $app->option('foo'));
    }

    public function testOptionWithDotNotation()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'mother' => [
                    'child' => 'test'
                ]
            ]
        ]);

        $this->assertEquals('test', $app->option('mother.child'));
    }

    public function testOptionFromPlugin()
    {
        App::destroy();
        App::plugin('namespace/plugin', [
            'options' => [
                'key' => 'A',
                'nested' => [
                    'key' => 'B'
                ]
            ]
        ]);

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->assertEquals('A', $app->option('namespace.plugin.key'));
        $this->assertEquals('B', $app->option('namespace.plugin.nested.key'));
        $this->assertEquals('B', $app->option('namespace.plugin.nested')['key']);
    }

    public function testOptions()
    {
        App::destroy();

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => $options = [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $this->assertEquals($options, $app->options());
    }

    public function testOptionsOnReady()
    {
        App::destroy();

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'ready' => function ($kirby) {
                    return [
                        'test' => $kirby->root('index')
                    ];
                }
            ]
        ]);

        $this->assertEquals('/dev/null', $app->option('test'));
    }

    public function testRolesFromFixtures()
    {
        $app = new App([
            'roots' => [
                'site' => __DIR__ . '/fixtures'
            ]
        ]);

        $this->assertInstanceOf(Roles::class, $app->roles());
    }

    // TODO: debug is not working properly
    // public function testEmail()
    // {
    //     $app = new App();
    //     $email = $app->email([
    //         'from' => 'no-reply@supercompany.com',
    //         'to' => 'someone@gmail.com',
    //         'subject' => 'Thank you for your contact request',
    //         'body' => 'We will never reply',
    //         'debug' => true
    //     ]);
    //     $this->assertInstanceOf(\Kirby\Email\Email::class, $email);
    // }

    public function testRoute()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home',
                    ],
                    [
                        'slug' => 'projects',
                    ]
                ]
            ]
        ]);

        $response = $app->call('projects');
        $route    = $app->route();

        $this->assertInstanceOf(Page::class, $response);
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testInstance()
    {
        $instance = new App();

        $this->assertEquals($instance, App::instance());
    }

    public function testFindPageFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'test',
                        'files' => [
                            ['filename' => 'test-a.jpg']
                        ]
                    ],
                ]
            ]
        ]);

        $page  = $app->page('test');
        $fileA = $page->file('test-a.jpg');
        $fileB = $page->file('test-b.jpg');

        // plain
        $this->assertEquals($fileA, $app->file('test/test-a.jpg'));

        // with page parent
        $this->assertEquals($fileA, $app->file('test-a.jpg', $page));

        // with file parent
        $this->assertEquals($fileB, $app->file('test-b.jpg', $fileA));
    }

    public function testFindSiteFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test-a.jpg'],
                    ['filename' => 'test-b.jpg']
                ]
            ]
        ]);

        $site  = $app->site();
        $fileA = $site->file('test-a.jpg');
        $fileB = $site->file('test-b.jpg');

        // plain
        $this->assertEquals($fileA, $app->file('test-a.jpg'));

        // with page parent
        $this->assertEquals($fileA, $app->file('test-a.jpg', $site));

        // with file parent
        $this->assertEquals($fileB, $app->file('test-b.jpg', $fileA));
    }

    public function testFindUserFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'files' => [
                        ['filename' => 'test-a.jpg'],
                        ['filename' => 'test-b.jpg']
                    ]
                ]
            ]
        ]);

        $user  = $app->user('test@getkirby.com');
        $fileA = $user->file('test-a.jpg');
        $fileB = $user->file('test-b.jpg');

        // with user parent
        $this->assertEquals($fileA, $app->file('test-a.jpg', $user));

        // with file parent
        $this->assertEquals($fileB, $app->file('test-b.jpg', $fileA));
    }

    public function testBlueprints()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
                'blueprints' => $fixtures = __DIR__ . '/fixtures/AppTest/blueprints',
            ],
            'blueprints' => [
                'pages/a' => ['title' => 'A'],
                'pages/d' => ['title' => 'C'],
                'files/a' => ['title' => 'File A']
            ]
        ]);

        Data::write($fixtures . '/pages/b.yml', ['title' => 'B']);
        Data::write($fixtures . '/pages/c.yml', ['title' => 'C']);
        Data::write($fixtures . '/files/b.yml', ['title' => 'File B']);

        $expected = [
            'a',
            'b',
            'c',
            'd',
            'default'
        ];

        $this->assertEquals($expected, $app->blueprints());

        $expected = [
            'a',
            'b',
            'default'
        ];

        $this->assertEquals($expected, $app->blueprints('files'));

        Dir::remove($fixtures);
    }

    public function testVersionHash()
    {
        $this->assertEquals(md5(App::version()), App::versionHash());
    }
}
