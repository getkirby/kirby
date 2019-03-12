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
}
