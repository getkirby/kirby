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
                'options'  => ['test' => 'Test'],
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
                'error'    => 'Invalid dropdown response',
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
