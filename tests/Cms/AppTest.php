<?php

namespace Kirby\Cms;

use Kirby\Http\Route;
use Kirby\Toolkit\F;

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

    public function testResolveHomePage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home'
                    ]
                ]
            ]
        ]);

        $result = $app->resolve(null);

        $this->assertInstanceOf(Page::class, $result);
        $this->assertTrue($result->isHomePage());
    }

    public function testResolveMainPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test'
                    ]
                ]
            ]
        ]);

        $result = $app->resolve('test');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test', $result->id());
    }

    public function testResolveSubPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'children' => [
                            ['slug' => 'subpage']
                        ]
                    ]
                ]
            ]
        ]);

        $result = $app->resolve('test/subpage');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test/subpage', $result->id());
    }

    public function testResolvePageRepresentation()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ]
        ]);

        // missing representation
        $result = $app->resolve('test.json');
        $this->assertNull($result);

        // xml presentation
        $result = $app->resolve('test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
    }

    public function testResolveSiteFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ],
            ]
        ]);

        // missing file
        $result = $app->resolve('test.png');
        $this->assertNull($result);

        // existing file
        $result = $app->resolve('test.jpg');

        $this->assertInstanceOf(File::class, $result);
        $this->assertEquals('test.jpg', $result->id());
    }

    public function testResolvePageFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ],
                    ]
                ]
            ]
        ]);

        // missing file
        $result = $app->resolve('test/test.png');
        $this->assertNull($result);

        // existing file
        $result = $app->resolve('test/test.jpg');

        $this->assertInstanceOf(File::class, $result);
        $this->assertEquals('test/test.jpg', $result->id());
    }
}
