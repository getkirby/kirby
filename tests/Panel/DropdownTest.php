<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Dropdown
 */
class DropdownTest extends TestCase
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

        // clean up $_SERVER
        unset($_SERVER['SERVER_SOFTWARE']);
    }

    /**
     * @covers ::changes
     */
    public function testChanges()
    {
        $this->app = $this->app->clone([
            'request' => [
                'body' => [
                    'ids' => [
                        'site',
                        'pages/test',
                        'pages/test/files/test.jpg',
                        'users/test'
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'title' => 'Test page'
                        ],
                        'files' => [
                            [
                                'filename' => 'test.jpg',
                            ]
                        ]
                    ]
                ],
                'content' => [
                    'title' => 'Test site'
                ]
            ],
            'users' => [
                ['email' => 'test@getkirby.com', 'id' => 'test']
            ]
        ]);

        $this->app->impersonate('kirby');

        $options = Dropdown::changes();
        $expected = [
            [
                'icon' => 'home',
                'text' => 'Test site',
                'link' => '/panel/site'
            ],
            [
                'icon' => 'page',
                'text' => 'Test page',
                'link' => '/panel/pages/test'
            ],
            [
                'icon' => 'image',
                'text' => 'test.jpg',
                'link' => '/panel/pages/test/files/test.jpg'
            ],
            [
                'icon' => 'user',
                'text' => 'test@getkirby.com',
                'link' => '/panel/users/test'
            ]
        ];

        $this->assertEquals($expected, $options);
    }

    /**
     * @covers ::changes
     */
    public function testChangesWithInvalidId()
    {
        $this->app = $this->app->clone([
            'request' => [
                'body' => [
                    'ids' => [
                        'site',
                        'pages/does-not-exist'
                    ]
                ]
            ],
            'site' => [
                'content' => [
                    'title' => 'Test site'
                ]
            ]
        ]);

        $this->app->impersonate('kirby');

        $options = Dropdown::changes();
        $expected = [
            [
                'icon' => 'home',
                'text' => 'Test site',
                'link' => '/panel/site'
            ]
        ];

        $this->assertEquals($expected, $options);
    }

    /**
     * @covers ::changes
     */
    public function testChangesWithLanguages()
    {
        $this->app = $this->app->clone([
            'options' => [
                'languages' => true,
            ],
            'request' => [
                'body' => [
                    'ids' => [
                        'site?language=en',
                    ]
                ]
            ],
            'site' => [
                'content' => [
                    'title' => 'Test site'
                ]
            ],
            'languages' => [
                ['code' => 'en', 'name' => 'English']
            ]
        ]);

        $this->app->impersonate('kirby');

        $options = Dropdown::changes();
        $expected = [
            [
                'icon' => 'home',
                'text' => 'Test site (en)',
                'link' => '/panel/site?language=en'
            ],
        ];

        $this->assertEquals($expected, $options);
    }

    /**
     * @covers ::changes
     */
    public function testChangesWithoutOptions()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionMessage('No changes for given models');

        Dropdown::changes();
    }

    /**
     * @covers ::error
     */
    public function testError(): void
    {
        // default
        $error = Dropdown::error('Test');

        $this->assertSame(404, $error['code']);
        $this->assertSame('Test', $error['error']);

        // custom code
        $error = Dropdown::error('Test', 500);

        $this->assertSame(500, $error['code']);
        $this->assertSame('Test', $error['error']);
    }

    /**
     * @covers ::response
     */
    public function testResponse(): void
    {
        $response = Dropdown::response([
            'test' => 'Test'
        ]);

        $expected = [
            '$dropdown' => [
                'options'  => ['Test'],
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
     * @covers ::response
     */
    public function testResponseFromInvalidData(): void
    {
        $response = Dropdown::response(1234);
        $expected = [
            '$dropdown' => [
                'code'     => 500,
                'error'    => 'Invalid response',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::response
     */
    public function testResponseFromException(): void
    {
        $exception = new \Exception('Test');
        $response  = Dropdown::response($exception);
        $expected  = [
            '$dropdown' => [
                'code'     => 500,
                'error'    => 'Test',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }

    /**
     * @covers ::response
     */
    public function testResponseFromKirbyException(): void
    {
        $exception = new \Kirby\Exception\NotFoundException('Test');
        $response  = Dropdown::response($exception);
        $expected  = [
            '$dropdown' => [
                'code'     => 404,
                'error'    => 'Test',
                'path'     => null,
                'referrer' => '/'
            ]
        ];

        $this->assertSame($expected, json_decode($response->body(), true));
    }
}
