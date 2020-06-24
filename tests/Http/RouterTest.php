<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function setUp(): void
    {
        Router::$beforeEach = null;
        Router::$afterEach  = null;
    }

    public function testRegisterSingleRoute()
    {
        $router = new Router([
            [
                'pattern' => '/',
                'method'  => 'GET',
                'action'  => function () {
                }
            ]
        ]);

        $result = $router->find('/', 'GET');

        $this->assertInstanceOf(Route::class, $result);
        $this->assertEquals('', $result->pattern());
        $this->assertEquals('GET', $result->method());
    }

    public function testRegisterMultipleRoutes()
    {
        $router = new Router([
            [
                'pattern' => 'a',
                'method'  => 'GET',
                'action'  => function () {
                }
            ],
            [
                'pattern' => 'b',
                'method'  => 'POST',
                'action'  => function () {
                }
            ]
        ]);

        $resultA = $router->find('a', 'GET');
        $resultB = $router->find('b', 'POST');

        $this->assertInstanceOf(Route::class, $resultA);
        $this->assertEquals('a', $resultA->pattern());
        $this->assertEquals('GET', $resultA->method());

        $this->assertInstanceOf(Route::class, $resultB);
        $this->assertEquals('b', $resultB->pattern());
        $this->assertEquals('POST', $resultB->method());
    }

    public function testRegisterInvalidRoute()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid route parameters');

        $router = new Router([
            'test' => 'test'
        ]);
    }

    public function testRegisterInvalidData()
    {
        $this->expectException('TypeError');

        $router = new Router('route');
    }

    public function testFindWithNonexistingMethod()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid routing method: KIRBY');
        $this->expectExceptionCode(400);

        $router = new Router();
        $router->find('a', 'KIRBY');
    }

    public function testFindNonexistingRoute()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No route found for path: "a" and request method: "GET"');
        $this->expectExceptionCode(404);

        $router = new Router();
        $router->find('a', 'GET');
    }

    public function testBeforeEach()
    {
        $router = new Router([
            [
                'pattern' => '/',
                'action'  => function () {
                }
            ]
        ]);

        $router::$beforeEach = function ($route, $path, $method) {
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals('/', $path);
            $this->assertEquals('GET', $method);
        };

        $router->call('/', 'GET');
    }

    public function testAfterEach()
    {
        $router = new Router([
            [
                'pattern' => '/',
                'action'  => function () {
                    return 'test';
                }
            ]
        ]);

        $router::$afterEach = function ($route, $path, $method, $result, $final) {
            $this->assertInstanceOf(Route::class, $route);
            $this->assertEquals('/', $path);
            $this->assertEquals('GET', $method);
            $this->assertEquals('test', $result);
            $this->assertTrue($final);

            return $result . ':after';
        };

        $this->assertEquals('test:after', $router->call('/', 'GET'));
    }

    public function testNext()
    {
        $router = new Router([
            [
                'pattern' => '(:any)',
                'action'  => function ($slug) {
                    if ($slug === 'a') {
                        return 'a';
                    }

                    /** @var \Kirby\Http\Route $this */
                    $this->next();
                }
            ],
            [
                'pattern' => '(:any)',
                'action'  => function ($slug) {
                    if ($slug === 'b') {
                        return 'b';
                    }

                    /** @var \Kirby\Http\Route $this */
                    $this->next();
                }
            ],
            [
                'pattern' => '(:any)',
                'action'  => function ($slug) {
                    if ($slug === 'c') {
                        return 'c';
                    }

                    /** @var \Kirby\Http\Route $this */
                    $this->next();
                }
            ]
        ]);

        $result = $router->call('a');
        $this->assertEquals('a', $result);

        $result = $router->call('b');
        $this->assertEquals('b', $result);

        $result = $router->call('c');
        $this->assertEquals('c', $result);

        $this->expectException('Exception');
        $this->expectExceptionMessage('No route found for path: "d" and request method: "GET"');

        $result = $router->call('d');
    }

    public function testNextAfterEach()
    {
        $router = new Router([
            [
                'pattern' => 'a',
                'action'  => function () {
                    /** @var \Kirby\Http\Route $this */
                    $this->next();
                }
            ],
            [
                'pattern' => 'a',
                'action'  => function () {
                    return 'a';
                }
            ]
        ]);

        $numTotal = 0;
        $numFinal = 0;

        $router::$afterEach = function ($route, $path, $method, $result, $final) use (&$numTotal, &$numFinal) {
            $numTotal++;

            if ($final === true) {
                $numFinal++;
            }
        };

        $router->call('a');
        $this->assertEquals(2, $numTotal);
        $this->assertEquals(1, $numFinal);
    }

    public function testCallWithCallback()
    {
        $router = new Router([
            [
                'pattern' => '(:any)',
                'action'  => function ($slug) {
                    // does not really get called
                }
            ],
        ]);

        $phpunit = $this;
        $result  = $router->call('test', 'GET', function ($route) use ($phpunit) {
            $phpunit->assertInstanceOf(Route::class, $route);
            return $route->arguments()[0];
        });

        $this->assertEquals('test', $result);
    }
}
