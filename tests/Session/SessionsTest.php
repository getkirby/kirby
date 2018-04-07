<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use Kirby\Http\Cookie;

require_once(__DIR__ . '/mocks.php');

class SessionsTest extends TestCase
{
    protected $store;
    protected $sessions;

    public function setUp()
    {
        $this->store    = new TestSessionStore();
        $this->sessions = new Sessions($this->store);
    }

    public function tearDown()
    {
        unset($this->sessions);
        unset($this->store);
    }

    public function testConstructorStores()
    {
        // mock store
        $this->assertEquals($this->store, $this->sessions->store());

        // custom store
        $store    = new FileSessionStore(__DIR__ . '/fixtures/store');
        $sessions = new Sessions($store);
        $this->assertEquals($store, $sessions->store());

        // custom path
        $path     = __DIR__ . '/fixtures/store';
        $sessions = new Sessions($path);

        $reflector = new ReflectionClass(FileSessionStore::class);
        $pathProperty = $reflector->getProperty('path');
        $pathProperty->setAccessible(true);
        $this->assertEquals($path, $pathProperty->getValue($sessions->store()));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testConstructorInvalidStore()
    {
        new Sessions(new InvalidSessionStore());
    }

    public function testConstructorOptions()
    {
        $sessions = new Sessions(__DIR__ . '/fixtures/store', [
            'mode'       => 'header',
            'cookieName' => 'my_cookie_name'
        ]);

        $this->assertEquals('my_cookie_name', $sessions->cookieName());

        $reflector = new ReflectionClass(Sessions::class);
        $modeProperty = $reflector->getProperty('mode');
        $modeProperty->setAccessible(true);
        $this->assertEquals('header', $modeProperty->getValue($sessions));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testConstructorInvalidMode()
    {
        new Sessions(__DIR__ . '/fixtures/store', ['mode' => 'invalid']);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testConstructorInvalidCookieName()
    {
        new Sessions(__DIR__ . '/fixtures/store', ['cookieName' => 123]);
    }

    public function testConstructorGarbageCollector()
    {
        // collect garbage every time
        $this->store->collectedGarbage = false;
        $sessions = new Sessions($this->store, ['gcInterval' => 1]);
        $this->assertTrue($this->store->collectedGarbage);

        // never collect garbage
        $this->store->collectedGarbage = false;
        $sessions = new Sessions($this->store, ['gcInterval' => false]);
        $this->assertFalse($this->store->collectedGarbage);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testConstructorInvalidGcInterval()
    {
        new Sessions(__DIR__ . '/fixtures/store', ['gcInterval' => 0]);
    }

    public function testCollectGarbage()
    {
        $this->store->collectedGarbage = false;
        $this->sessions->collectGarbage();
        $this->assertTrue($this->store->collectedGarbage);
    }

    public function testTokenFromCookie()
    {
        $reflector = new ReflectionClass(Sessions::class);
        $tokenFromCookie = $reflector->getMethod('tokenFromCookie');
        $tokenFromCookie->setAccessible(true);

        Cookie::remove('kirby_session');
        $this->assertNull($tokenFromCookie->invoke($this->sessions));

        Cookie::set('kirby_session', 'amazingSessionIdFromCookie');
        $this->assertEquals('amazingSessionIdFromCookie', $tokenFromCookie->invoke($this->sessions));

        Cookie::remove('kirby_session');
    }

    public function testTokenFromHeader()
    {
        $reflector = new ReflectionClass(Sessions::class);
        $tokenFromHeader = $reflector->getMethod('tokenFromHeader');
        $tokenFromHeader->setAccessible(true);

        unset($_SERVER['HTTP_AUTHORIZATION']);
        $this->assertNull($tokenFromHeader->invoke($this->sessions));

        $_SERVER['HTTP_AUTHORIZATION'] = 'Session amazingSessionIdFromHeader';
        $this->assertEquals('amazingSessionIdFromHeader', $tokenFromHeader->invoke($this->sessions));

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer amazingSessionIdFromHeader';
        $this->assertNull($tokenFromHeader->invoke($this->sessions));

        unset($_SERVER['HTTP_AUTHORIZATION']);
    }
}
