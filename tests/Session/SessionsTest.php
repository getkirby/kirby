<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use Kirby\Http\Cookie;

require_once(__DIR__ . '/mocks.php');

/**
 * @coversDefaultClass \Kirby\Session\Sessions
 */
class SessionsTest extends TestCase
{
    protected $store;
    protected $sessions;

    public function setUp(): void
    {
        $this->store    = new TestSessionStore();
        $this->sessions = new Sessions($this->store);

        MockTime::$time = 1337000000;
    }

    public function tearDown(): void
    {
        unset($this->sessions);
        unset($this->store);
    }

    /**
     * @covers ::__construct
     * @covers ::store
     */
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
     * @covers ::__construct
     */
    public function testConstructorInvalidStore()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Sessions(new InvalidSessionStore());
    }

    /**
     * @covers ::__construct
     * @covers ::cookieName
     */
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
     * @covers ::__construct
     */
    public function testConstructorInvalidMode()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Sessions(__DIR__ . '/fixtures/store', ['mode' => 'invalid']);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorInvalidCookieName()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Sessions(__DIR__ . '/fixtures/store', ['cookieName' => 123]);
    }

    /**
     * @covers ::__construct
     */
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
     * @covers ::__construct
     */
    public function testConstructorInvalidGcInterval()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Sessions(__DIR__ . '/fixtures/store', ['gcInterval' => 0]);
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $sessions = new Sessions($this->store, ['mode' => 'header']);
        $session = $sessions->create();
        $this->assertEquals('header', $session->mode());
        $this->assertNull($session->token());
        $this->assertEquals(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertEquals(7200, $session->duration());
        $this->assertEquals(1337000000 + 7200, $session->expiryTime()); // timestamp is from mock
        $this->assertEquals(1800, $session->timeout());
        $this->assertTrue($session->renewable());

        $session = $sessions->create([
            'mode'       => 'manual',
            'startTime'  => '+ 1 hour',
            'expiryTime' => '+ 10 hours',
            'timeout'    => false,
            'renewable'  => false
        ]);
        $this->assertEquals('manual', $session->mode());
        $this->assertNull($session->token());
        $this->assertEquals(1337000000 + 3600, $session->startTime()); // timestamp is from mock
        $this->assertEquals(36000, $session->duration());
        $this->assertEquals(1337000000 + 39600, $session->expiryTime()); // timestamp is from mock
        $this->assertEquals(false, $session->timeout());
        $this->assertFalse($session->renewable());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $sessions = new Sessions($this->store, ['mode' => 'header']);
        $session = $sessions->get('9999999999.valid.' . $this->store->validKey);
        $this->assertEquals('header', $session->mode());
        $this->assertEquals('9999999999.valid.' . $this->store->validKey, $session->token());

        $session1 = $sessions->get('9999999999.valid2.' . $this->store->validKey, 'manual');
        $this->assertEquals('manual', $session1->mode());
        $this->assertEquals('9999999999.valid2.' . $this->store->validKey, $session1->token());

        $session2 = $sessions->get('9999999999.valid2.' . $this->store->validKey, 'header');
        $this->assertEquals($session1, $session2);
        $session1->data()->set('someKey', 'someValue');
        $this->assertEquals('someValue', $session2->data()->get('someKey'));
    }

    /**
     * @covers ::get
     */
    public function testGetInvalid()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.session.notFound');

        $this->sessions->get('9999999999.doesNotExist.' . $this->store->validKey);
    }

    /**
     * @covers ::current
     */
    public function testCurrent()
    {
        Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->validKey;

        $sessions = new Sessions($this->store, ['mode' => 'cookie']);
        $session = $sessions->current();
        $this->assertEquals('cookie', $session->mode());
        $this->assertEquals('9999999999.valid.' . $this->store->validKey, $session->token());

        $sessions = new Sessions($this->store, ['mode' => 'header']);
        $session = $sessions->current();
        $this->assertEquals('header', $session->mode());
        $this->assertEquals('9999999999.valid2.' . $this->store->validKey, $session->token());

        unset($_SERVER['HTTP_AUTHORIZATION']);
        $this->assertNull($sessions->current());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->invalidKey;
        $this->assertNull($sessions->current());

        // test self-check: should work again
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->validKey;
        $session = $sessions->current();
        $this->assertEquals('header', $session->mode());
        $this->assertEquals('9999999999.valid2.' . $this->store->validKey, $session->token());
    }

    /**
     * @covers ::current
     */
    public function testCurrentManualMode()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.sessions.manualMode');

        $sessions = new Sessions($this->store, ['mode' => 'manual']);
        $sessions->current();
    }

    /**
     * @covers ::currentDetected
     */
    public function testCurrentDetected()
    {
        Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->validKey;

        $session = $this->sessions->currentDetected();
        $this->assertEquals('header', $session->mode());
        $this->assertEquals('9999999999.valid2.' . $this->store->validKey, $session->token());

        unset($_SERVER['HTTP_AUTHORIZATION']);
        $session = $this->sessions->currentDetected();
        $this->assertEquals('cookie', $session->mode());
        $this->assertEquals('9999999999.valid.' . $this->store->validKey, $session->token());

        Cookie::remove('kirby_session');
        $this->assertNull($this->sessions->currentDetected());

        Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->invalidKey;
        $this->assertNull($this->sessions->currentDetected());

        // test self-check: should work again
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->validKey;
        $session = $this->sessions->currentDetected();
        $this->assertEquals('header', $session->mode());
        $this->assertEquals('9999999999.valid2.' . $this->store->validKey, $session->token());
    }

    /**
     * @covers ::collectGarbage
     */
    public function testCollectGarbage()
    {
        $this->store->collectedGarbage = false;
        $this->sessions->collectGarbage();
        $this->assertTrue($this->store->collectedGarbage);
    }

    /**
     * @covers ::tokenFromCookie
     */
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

    /**
     * @covers ::tokenFromHeader
     */
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
