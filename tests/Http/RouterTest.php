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

    /**
     * @expectedException TypeError
     */
    public function testRegisterInvalidData()
    {
        $router = new Router('route');
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid routing method: KIRBY
     * @expectedExceptionCode    400
     */
    public function testFindWithNonexistingMethod()
    {
        $router = new Router;
        $router->find('a', 'KIRBY');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No route found for path: "a" and request method: "GET"
     * @expectedExceptionCode    404
     */
    public function testFindNonexistingRoute()
    {
        $router = new Router;
        $router->find('a', 'GET');
    }
}
