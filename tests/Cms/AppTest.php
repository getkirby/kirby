<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Http\Route;
use Kirby\Toolkit\F;

/**
 * @coversDefaultClass \Kirby\Cms\App
 */
class AppTest extends TestCase
{
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
                    'singleParam' => [
                        function ($value) {
                            if (func_num_args() !== 1) {
                                throw new \Exception();
                            }

                            return $value * 2;
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
                            return $value + 1;
                        },
                    ]
                ]
            ]
        ]);

        $this->assertEquals(5, $app->apply('singleParam', 2));
        $this->assertEquals(21, $app->apply('singleParam', 10));

        $this->assertEquals(5, $app->apply('multiParams', 'arg1', 'arg2', 2));
        $this->assertEquals(21, $app->apply('multiParams', 'arg1', 'arg2', 10));

        $this->assertEquals(2, $app->apply('does-not-exist', 2));
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

    public function testIoWithString()
    {
        $result = kirby()->io('test');

        $this->assertEquals('test', $result->body());
        $this->assertEquals(200, $result->code());
        $this->assertEquals('text/html', $result->type());
    }

    public function testIoWithArray()
    {
        $input  = ['test' => 'response'];
        $result = kirby()->io($input);

        $this->assertEquals(json_encode($input), $result->body());
        $this->assertEquals(200, $result->code());
        $this->assertEquals('application/json', $result->type());
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
}
