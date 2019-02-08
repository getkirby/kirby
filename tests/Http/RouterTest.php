<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
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

        $router = new Router;
        $router->find('a', 'KIRBY');
    }

    public function testFindNonexistingRoute()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No route found for path: "a" and request method: "GET"');
        $this->expectExceptionCode(404);

        $router = new Router;
        $router->find('a', 'GET');
    }
}
