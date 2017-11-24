<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

use Kirby\Http\Router\Route;

class RouterTest extends TestCase
{

    public function testRegisterSingleRoute()
    {
        $router = new Router;
        $route  = new Route('/', 'GET', function () {});
        $router->register($route);
        $result = $router->find('/', 'GET');

        $this->assertInstanceOf('Kirby\Http\Router\Result', $result);
        $this->assertEquals('/', $result->pattern());
        $this->assertEquals('GET', $result->method());
    }

    public function testRegisterMultipleRoutes()
    {
        $router = new Router;
        $a      = new Route('a', 'GET', function () {});
        $b      = new Route('b', 'POST', function () {});

        $router->register([$a, $b]);

        $resultA = $router->find('a', 'GET');
        $resultB = $router->find('b', 'POST');

        $this->assertInstanceOf('Kirby\Http\Router\Result', $resultA);
        $this->assertEquals('a', $resultA->pattern());
        $this->assertEquals('GET', $resultA->method());

        $this->assertInstanceOf('Kirby\Http\Router\Result', $resultB);
        $this->assertEquals('b', $resultB->pattern());
        $this->assertEquals('POST', $resultB->method());
    }

    public function testDependency()
    {
        $router = new Router;
        $router->dependency('Test', 'TestDependency');

        $route  = new Route('a', 'GET', function ($test) {
            print $test;
        });
        $router->register($route);

        $this->expectOutputString('TestDependency');
        $router->call('a');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid data to register routes
     */
    public function testRegisterInvalidData()
    {
        $router = new Router;
        $router->register('route');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid routing method: KIRBY
     */
    public function testFindWithNonexistingMethod()
    {
        $router = new Router;
        $router->find('a', 'KIRBY');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No route found for path: "a" and request method: "GET"
     */
    public function testFindNonexistingRoute()
    {
        $router = new Router;
        $router->find('a', 'GET');
    }
}
